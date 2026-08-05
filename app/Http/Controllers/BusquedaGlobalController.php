<?php

namespace App\Http\Controllers;

use App\Http\Requests\BusquedaGlobalRequest;
use App\Models\Comunidad;
use App\Models\DiarioApunte;
use App\Models\Parte;
use App\Models\Propietario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class BusquedaGlobalController extends Controller
{
    public function __invoke(BusquedaGlobalRequest $request): JsonResponse
    {
        $term = (string) $request->validated('q');
        $tipo = (string) ($request->validated('tipo') ?? 'todos');
        $comunidades = Comunidad::query()->visibleTo($request->user())->select('id');
        $resultados = collect();

        if ($tipo === 'todos' || $tipo === 'comunidades') {
            $resultados->push(...$this->comunidades($request, $term));
        }
        if ($tipo === 'todos' || $tipo === 'partes') {
            $resultados->push(...$this->partes($comunidades, $term));
        }
        if ($tipo === 'todos' || $tipo === 'propietarios') {
            $resultados->push(...$this->propietarios($request, $term));
        }
        if ($tipo === 'todos' || $tipo === 'diario') {
            $resultados->push(...$this->diario($comunidades, $term));
        }

        return response()->json(['resultados' => $resultados->take(24)->values()]);
    }

    private function comunidades(BusquedaGlobalRequest $request, string $term): Collection
    {
        return Comunidad::query()->visibleTo($request->user())
            ->where(fn ($query) => $query->where('nombre', 'like', "%{$term}%")
                ->orWhere('codigo', 'like', "%{$term}%")
                ->orWhere('nif', 'like', "%{$term}%"))
            ->orderBy('nombre')->limit(6)->get(['id', 'nombre', 'codigo'])
            ->map(fn (Comunidad $comunidad): array => [
                'tipo' => 'Comunidad', 'titulo' => $comunidad->nombre,
                'detalle' => $comunidad->codigo, 'url' => route('comunidades.show', $comunidad),
            ]);
    }

    /** @param Builder<Comunidad> $comunidades */
    private function partes(Builder $comunidades, string $term): Collection
    {
        return Parte::query()->whereIn('comunidad_id', clone $comunidades)
            ->where(fn ($query) => $query->where('codigo', 'like', "%{$term}%")
                ->orWhere('descripcion', 'like', "%{$term}%"))
            ->with('comunidad:id,nombre')->orderBy('codigo')->limit(6)->get(['id', 'comunidad_id', 'codigo', 'descripcion'])
            ->map(fn (Parte $parte): array => [
                'tipo' => 'Parte', 'titulo' => trim("{$parte->codigo} {$parte->descripcion}"),
                'detalle' => $parte->comunidad->nombre, 'url' => route('partes.show', $parte),
            ]);
    }

    private function propietarios(BusquedaGlobalRequest $request, string $term): Collection
    {
        return Propietario::query()->visibleTo($request->user())
            ->where(fn ($query) => $query->where('nombre', 'like', "%{$term}%")
                ->orWhere('nif', 'like', "%{$term}%")
                ->orWhere('emails', 'like', "%{$term}%"))
            ->orderBy('nombre')->limit(6)->get(['id', 'nombre', 'nif'])
            ->map(fn (Propietario $propietario): array => [
                'tipo' => 'Propietario', 'titulo' => $propietario->nombre,
                'detalle' => $propietario->nif, 'url' => route('propietarios.show', $propietario),
            ]);
    }

    /** @param Builder<Comunidad> $comunidades */
    private function diario(Builder $comunidades, string $term): Collection
    {
        return DiarioApunte::query()->whereIn('comunidad_id', clone $comunidades)
            ->where(fn ($query) => $query->where('descripcion', 'like', "%{$term}%")
                ->orWhere('numero_documento', 'like', "%{$term}%"))
            ->with(['comunidad:id,nombre', 'parte:id,codigo'])->latest('fecha')->limit(6)
            ->get(['id', 'comunidad_id', 'parte_id', 'fecha', 'numero_documento', 'descripcion'])
            ->map(fn (DiarioApunte $apunte): array => [
                'tipo' => 'Diario', 'titulo' => $apunte->descripcion,
                'detalle' => trim($apunte->comunidad->nombre.' · '.($apunte->parte?->codigo ?? '').' · '.$apunte->fecha->format('d/m/Y'), ' ·'),
                'url' => route('diario.show', ['comunidad' => $apunte->comunidad_id, 'parte' => $apunte->parte_id, 'apunte' => $apunte->id]),
            ]);
    }
}
