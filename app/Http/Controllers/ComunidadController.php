<?php

namespace App\Http\Controllers;

use App\Actions\ImportComunidadData;
use App\Http\Requests\IndexComunidadRequest;
use App\Http\Requests\StoreComunidadRequest;
use App\Http\Requests\UpdateComunidadRequest;
use App\Models\Comunidad;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ComunidadController extends Controller
{
    public function index(IndexComunidadRequest $request): Response
    {
        Gate::authorize('viewAny', Comunidad::class);

        return Inertia::render('Comunidades/Index', [
            'comunidades' => Comunidad::query()
                ->select(['id', 'codigo', 'nombre', 'nif', 'direccion', 'poblacion'])
                ->visibleTo(request()->user())
                ->withCount('partes')
                ->orderBy($request->sortColumn(), $request->sortDirection())
                ->orderBy('id')
                ->paginate(20)
                ->withQueryString(),
            'orden' => [
                'columna' => $request->sortColumn(),
                'direccion' => $request->sortDirection(),
            ],
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Comunidad::class);

        return Inertia::render('Comunidades/Form');
    }

    public function store(StoreComunidadRequest $request, ImportComunidadData $importComunidadData): RedirectResponse
    {
        $validated = $request->validated();
        $bancos = $this->normalizeBancos($validated['bancos'] ?? [], $validated['banco_principal'] ?? null);
        $comunidadData = Arr::except($validated, ['bancos', 'banco_principal', 'archivo']);
        $comunidadData['administracion_id'] = $request->user()->managedAdministracionId();
        $archivo = $request->file('archivo');

        $comunidad = DB::transaction(function () use ($comunidadData, $bancos, $archivo, $importComunidadData): Comunidad {
            $comunidad = Comunidad::create($comunidadData);
            $comunidad->bancos()->createMany($bancos);

            if ($archivo instanceof UploadedFile) {
                $importComunidadData->handle($comunidad, $archivo);
            }

            return $comunidad;
        });

        $this->flashSuccess('Comunidad creada.');

        return to_route('comunidades.show', $comunidad);
    }

    public function show(Comunidad $comunidad): Response
    {
        Gate::authorize('view', $comunidad);
        $comunidad->load(['bancos' => fn ($query) => $query->orderByDesc('es_principal')->orderBy('nombre')]);

        return Inertia::render('Comunidades/Show', ['comunidad' => $comunidad]);
    }

    public function edit(Comunidad $comunidad): Response
    {
        Gate::authorize('update', $comunidad);
        $comunidad->load(['bancos' => fn ($query) => $query->orderByDesc('es_principal')->orderBy('nombre')]);

        return Inertia::render('Comunidades/Form', ['comunidad' => $comunidad]);
    }

    public function update(UpdateComunidadRequest $request, Comunidad $comunidad): RedirectResponse
    {
        Gate::authorize('update', $comunidad);
        $validated = $request->validated();
        $bancos = $this->normalizeBancos($validated['bancos'] ?? [], $validated['banco_principal'] ?? null);
        $comunidadData = Arr::except($validated, ['bancos', 'banco_principal']);

        DB::transaction(function () use ($comunidad, $comunidadData, $bancos): void {
            $comunidad->update($comunidadData);
            $comunidad->bancos()->delete();
            $comunidad->bancos()->createMany($bancos);
        });

        $this->flashSuccess('Comunidad actualizada.');

        return to_route('comunidades.show', $comunidad);
    }

    public function destroy(Comunidad $comunidad): RedirectResponse
    {
        Gate::authorize('delete', $comunidad);
        $comunidad->delete();

        $this->flashSuccess('Comunidad archivada.');

        return to_route('comunidades.index');
    }

    /**
     * @param  array<int, array<string, mixed>>  $bancos
     * @return array<int, array<string, mixed>>
     */
    private function normalizeBancos(array $bancos, ?int $bancoPrincipal): array
    {
        return collect($bancos)
            ->map(function (array $banco, int $index) use ($bancoPrincipal): array {
                $banco['es_principal'] = $index === $bancoPrincipal;

                return $banco;
            })
            ->filter(fn (array $banco): bool => collect(Arr::except($banco, ['es_principal']))
                ->contains(fn (mixed $value): bool => filled($value)))
            ->values()
            ->all();
    }
}
