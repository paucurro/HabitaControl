<?php

namespace App\Http\Controllers;

use App\Actions\Diario\ActualizarApunteDiario;
use App\Actions\Diario\EliminarApunteDiario;
use App\Actions\Diario\GuardarApuntesDiario;
use App\Actions\Diario\TraspasarApunteDiario;
use App\Http\Requests\DiarioIndexRequest;
use App\Http\Requests\StoreDiarioApuntesRequest;
use App\Http\Requests\TransferDiarioApunteRequest;
use App\Http\Requests\UpdateDiarioApunteRequest;
use App\Models\Comunidad;
use App\Models\DiarioApunte;
use App\Models\DiarioApunteEspecial;
use App\Models\DiarioObra;
use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
            'comunidades' => $this->visibleCommunities($request),
        ]);
    }

    public function show(DiarioIndexRequest $request, Comunidad $comunidad): Response
    {
        $tipo = $request->string('tipo')->value() ?: 'apuntes';
        $orden = $request->input('orden') === 'asc' ? 'asc' : 'desc';
        $parteId = $request->integer('parte') ?: null;
        $apunteId = $request->integer('apunte') ?: null;
        $desde = $request->date('desde')?->toDateString();
        $hasta = $request->date('hasta')?->toDateString();
        abort_if($parteId !== null && ! $comunidad->partes()->whereKey($parteId)->exists(), 404);
        abort_if($apunteId !== null && ($tipo !== 'apuntes' || ! DiarioApunte::query()->whereBelongsTo($comunidad)->whereKey($apunteId)->exists()), 404);

        $query = $this->query($comunidad, $tipo)
            ->when(
                $parteId !== null,
                fn (Builder $query) => $query->where('parte_id', $parteId),
                fn (Builder $query) => $query->whereNull('parte_id'),
            )
            ->when($desde, fn (Builder $query) => $query->whereDate('fecha', '>=', $desde))
            ->when($hasta, fn (Builder $query) => $query->whereDate('fecha', '<=', $hasta))
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
                'desde' => $desde,
                'hasta' => $hasta,
            ],
            'saldoSeleccionado' => $this->selectedBalance($comunidad, $tipo, $parteId, $desde, $hasta),
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

    public function update(
        UpdateDiarioApunteRequest $request,
        Comunidad $comunidad,
        string $tipo,
        int $apunte,
        ActualizarApunteDiario $actualizar,
    ): RedirectResponse {
        $validated = $request->validated();
        $actualizar->handle($comunidad, $tipo, $apunte, $validated['apuntes'][0]);
        $this->flashSuccess('Apunte actualizado.');

        return back();
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

    public function destroy(Comunidad $comunidad, int $apunte, EliminarApunteDiario $eliminar): RedirectResponse
    {
        Gate::authorize('update', $comunidad);
        $eliminar->handle($comunidad, $apunte);
        $this->flashSuccess('Apunte eliminado.');

        return back();
    }

    /** @return Builder<DiarioApunte>|Builder<DiarioApunteEspecial>|Builder<DiarioObra> */
    private function query(Comunidad $comunidad, string $tipo): Builder
    {
        if ($tipo === 'especiales') {
            return DiarioApunteEspecial::query()
                ->whereBelongsTo($comunidad)
                ->select([
                    'id', 'comunidad_id', 'tipo_gasto_id', 'parte_id', 'proveedor_id', 'liquidacion_id',
                    'tipo', 'fecha', 'fecha_calculada', 'descripcion', 'importe', 'base_imponible', 'porcentaje_iva',
                ])
                ->selectRaw('SUM(importe) OVER (ORDER BY fecha ASC, id ASC) AS saldo')
                ->withCasts(['saldo' => 'decimal:4'])
                ->with(['parte:id,codigo,descripcion', 'tipoGasto:id,codigo,descripcion', 'proveedor:id,nombre']);
        }

        if ($tipo === 'obras') {
            return DiarioObra::query()
                ->whereBelongsTo($comunidad)
                ->select([
                    'id', 'comunidad_id', 'tipo_obra_id', 'tipo_gasto_id', 'banco_id', 'parte_id', 'proveedor_id',
                    'liquidacion_id', 'fecha', 'fecha_calculada', 'numero_documento', 'descripcion', 'debe', 'haber',
                    'base_imponible', 'porcentaje_iva',
                ])
                ->selectRaw('SUM(debe - haber) OVER (ORDER BY fecha ASC, id ASC) AS saldo')
                ->withCasts(['saldo' => 'decimal:4'])
                ->with([
                    'parte:id,codigo,descripcion', 'tipoGasto:id,codigo,descripcion', 'tipoObra:id,codigo,descripcion',
                    'banco:id,codigo_interno,nombre', 'proveedor:id,nombre',
                ]);
        }

        return DiarioApunte::query()
            ->whereBelongsTo($comunidad)
            ->select([
                'id', 'comunidad_id', 'tipo_gasto_id', 'banco_id', 'parte_id', 'proveedor_id', 'liquidacion_id',
                'fecha', 'fecha_calculada', 'numero_documento', 'descripcion', 'debe', 'haber', 'base_imponible', 'porcentaje_iva',
            ])
            ->selectRaw('SUM(debe - haber) OVER (ORDER BY fecha ASC, id ASC) AS saldo')
            ->withCasts(['saldo' => 'decimal:4'])
            ->with(['parte:id,codigo,descripcion', 'tipoGasto:id,codigo,descripcion', 'banco:id,codigo_interno,nombre', 'proveedor:id,nombre']);
    }

    private function selectedBalance(
        Comunidad $comunidad,
        string $tipo,
        ?int $parteId,
        ?string $desde,
        ?string $hasta,
    ): string {
        $query = match ($tipo) {
            'apuntes' => DiarioApunte::query()->whereBelongsTo($comunidad),
            'especiales' => DiarioApunteEspecial::query()->whereBelongsTo($comunidad),
            'obras' => DiarioObra::query()->whereBelongsTo($comunidad),
            default => throw new InvalidArgumentException('Tipo de diario no válido.'),
        };

        $query
            ->when(
                $parteId !== null,
                fn (Builder $query) => $query->where('parte_id', $parteId),
                fn (Builder $query) => $query->whereNull('parte_id'),
            )
            ->when($desde, fn (Builder $query) => $query->whereDate('fecha', '>=', $desde))
            ->when($hasta, fn (Builder $query) => $query->whereDate('fecha', '<=', $hasta));

        $saldo = match ($tipo) {
            'apuntes', 'obras' => $query->selectRaw('COALESCE(SUM(debe - haber), 0) AS saldo')->value('saldo'),
            'especiales' => $query->sum('importe'),
        };

        return number_format((float) $saldo, 4, '.', '');
    }

    /** @return Collection<int, Comunidad> */
    private function visibleCommunities(Request $request): Collection
    {
        return Comunidad::query()
            ->visibleTo($request->user())
            ->orderBy('nombre')
            ->orderBy('id')
            ->get(['id', 'codigo', 'nombre']);
    }
}
