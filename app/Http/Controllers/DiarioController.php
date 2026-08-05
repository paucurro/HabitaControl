<?php

namespace App\Http\Controllers;

use App\Actions\Diario\GuardarApuntesDiario;
use App\Actions\Diario\TraspasarApunteDiario;
use App\Http\Requests\DiarioIndexRequest;
use App\Http\Requests\StoreDiarioApuntesRequest;
use App\Http\Requests\TransferDiarioApunteRequest;
use App\Models\Comunidad;
use App\Models\DiarioApunte;
use App\Models\DiarioApunteEspecial;
use App\Models\DiarioObra;
use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

class DiarioController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Comunidad::class);

        return Inertia::render('Diario/Index', [
            'comunidades' => Comunidad::query()
                ->visibleTo($request->user())
                ->orderBy('nombre')
                ->orderBy('id')
                ->get(['id', 'codigo', 'nombre']),
        ]);
    }

    public function show(DiarioIndexRequest $request, Comunidad $comunidad): Response
    {
        $tipo = $request->string('tipo')->value() ?: 'apuntes';
        $orden = $request->input('orden') === 'asc' ? 'asc' : 'desc';
        $parteId = $request->integer('parte') ?: null;
        $apunteId = $request->integer('apunte') ?: null;
        abort_if($parteId !== null && ! $comunidad->partes()->whereKey($parteId)->exists(), 404);
        abort_if($apunteId !== null && ($tipo !== 'apuntes' || ! DiarioApunte::query()->whereBelongsTo($comunidad)->whereKey($apunteId)->exists()), 404);

        $query = $this->query($comunidad, $tipo)
            ->when($parteId, fn (Builder $query) => $query->where('parte_id', $parteId))
            ->when($request->date('desde'), fn (Builder $query, $desde) => $query->whereDate('fecha', '>=', $desde))
            ->when($request->date('hasta'), fn (Builder $query, $hasta) => $query->whereDate('fecha', '<=', $hasta))
            ->when($apunteId, fn (Builder $query) => $query->whereKey($apunteId));

        return Inertia::render('Comunidades/Diario', [
            'comunidad' => $comunidad->only(['id', 'codigo', 'nombre']),
            'partes' => $comunidad->partes()->orderBy('codigo')->get(['id', 'codigo', 'descripcion']),
            'catalogos' => [
                'tiposGasto' => $comunidad->tiposGasto()->orderBy('codigo')->get(['id', 'codigo', 'descripcion']),
                'bancos' => $comunidad->bancos()->orderByDesc('es_principal')->orderBy('nombre')->get(['id', 'codigo_interno', 'nombre']),
                'tiposObra' => $comunidad->tiposObra()->orderBy('codigo')->get(['id', 'codigo', 'descripcion']),
                'proveedores' => Proveedor::query()->where('administracion_id', $comunidad->administracion_id)->orderBy('nombre')->get(['id', 'nombre']),
            ],
            'filtros' => [
                'tipo' => $tipo,
                'parte' => $parteId,
                'apunte' => $apunteId,
                'orden' => $orden,
                'desde' => $request->date('desde')?->toDateString(),
                'hasta' => $request->date('hasta')?->toDateString(),
            ],
            'saldoComunidad' => $this->communityBalance($comunidad, $tipo),
            'puedeGestionar' => $request->user()?->can('update', $comunidad) ?? false,
            'apuntes' => $query->orderBy('fecha', $orden)->orderBy('id', $orden)->paginate(50)->withQueryString(),
        ]);
    }

    public function store(StoreDiarioApuntesRequest $request, Comunidad $comunidad, GuardarApuntesDiario $guardar): RedirectResponse
    {
        $validated = $request->validated();
        $guardar->handle($comunidad, $validated['tipo'], $validated['apuntes']);
        $this->flashSuccess(count($validated['apuntes']) === 1 ? 'Apunte guardado.' : count($validated['apuntes']).' apuntes guardados.');

        return to_route('diario.show', ['comunidad' => $comunidad, 'tipo' => $validated['tipo']]);
    }

    public function transfer(
        TransferDiarioApunteRequest $request,
        Comunidad $comunidad,
        string $tipo,
        int $apunte,
        TraspasarApunteDiario $traspasar,
    ): RedirectResponse {
        $validated = $request->validated();
        $traspasar->handle($comunidad, $tipo, $apunte, $validated['destino'], $validated['tipo_obra_id'] ?? null);
        $this->flashSuccess('Apunte traspasado correctamente.');

        return to_route('diario.show', ['comunidad' => $comunidad, 'tipo' => $validated['destino']]);
    }

    /** @return Builder<DiarioApunte>|Builder<DiarioApunteEspecial>|Builder<DiarioObra> */
    private function query(Comunidad $comunidad, string $tipo): Builder
    {
        if ($tipo === 'especiales') {
            return DiarioApunteEspecial::query()
                ->whereBelongsTo($comunidad)
                ->select([
                    'id', 'comunidad_id', 'tipo_gasto_id', 'parte_id', 'proveedor_id', 'liquidacion_id',
                    'tipo', 'fecha', 'descripcion', 'importe', 'base_imponible', 'porcentaje_iva',
                ])
                ->selectRaw('SUM(importe) OVER (ORDER BY fecha ASC, id ASC) AS saldo')
                ->withCasts(['saldo' => 'decimal:4'])
                ->with(['parte:id,codigo', 'tipoGasto:id,codigo,descripcion', 'proveedor:id,nombre']);
        }

        if ($tipo === 'obras') {
            return DiarioObra::query()
                ->whereBelongsTo($comunidad)
                ->select([
                    'id', 'comunidad_id', 'tipo_obra_id', 'tipo_gasto_id', 'banco_id', 'parte_id', 'proveedor_id',
                    'liquidacion_id', 'fecha', 'numero_documento', 'descripcion', 'debe', 'haber',
                    'base_imponible', 'porcentaje_iva',
                ])
                ->selectRaw('SUM(debe - haber) OVER (ORDER BY fecha ASC, id ASC) AS saldo')
                ->withCasts(['saldo' => 'decimal:4'])
                ->with([
                    'parte:id,codigo', 'tipoGasto:id,codigo,descripcion', 'tipoObra:id,codigo,descripcion',
                    'banco:id,codigo_interno,nombre', 'proveedor:id,nombre',
                ]);
        }

        return DiarioApunte::query()
            ->whereBelongsTo($comunidad)
            ->select([
                'id', 'comunidad_id', 'tipo_gasto_id', 'banco_id', 'parte_id', 'proveedor_id', 'liquidacion_id',
                'fecha', 'numero_documento', 'descripcion', 'debe', 'haber', 'base_imponible', 'porcentaje_iva',
            ])
            ->selectRaw('SUM(debe - haber) OVER (ORDER BY fecha ASC, id ASC) AS saldo')
            ->withCasts(['saldo' => 'decimal:4'])
            ->with(['parte:id,codigo', 'tipoGasto:id,codigo,descripcion', 'banco:id,codigo_interno,nombre', 'proveedor:id,nombre']);
    }

    private function communityBalance(Comunidad $comunidad, string $tipo): string
    {
        $saldo = match ($tipo) {
            'apuntes' => DiarioApunte::query()->whereBelongsTo($comunidad)
                ->selectRaw('COALESCE(SUM(debe - haber), 0) AS saldo')->value('saldo'),
            'especiales' => DiarioApunteEspecial::query()->whereBelongsTo($comunidad)->sum('importe'),
            'obras' => DiarioObra::query()->whereBelongsTo($comunidad)
                ->selectRaw('COALESCE(SUM(debe - haber), 0) AS saldo')->value('saldo'),
            default => throw new InvalidArgumentException('Tipo de diario no válido.'),
        };

        return number_format((float) $saldo, 4, '.', '');
    }
}
