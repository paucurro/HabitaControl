<?php

namespace App\Http\Controllers;

use App\Actions\ImportComunidadData;
use App\Http\Requests\ImportComunidadDataRequest;
use App\Models\Comunidad;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ComunidadDataController extends Controller
{
    public function export(Comunidad $comunidad): StreamedResponse
    {
        Gate::authorize('view', $comunidad);
        $filename = "comunidad-{$comunidad->codigo}.csv";

        return response()->streamDownload(function () use ($comunidad): void {
            $output = fopen('php://output', 'w');
            if ($output === false) {
                throw new RuntimeException('No se ha podido generar el archivo de exportación.');
            }
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['comunidad_codigo', 'comunidad_nombre', 'parte_codigo', 'parte_descripcion', 'coeficiente', 'propietario_nombre', 'propietario_nif', 'email', 'direccion', 'codigo_postal', 'poblacion', 'provincia', 'telefono', 'movil'], ';');
            $comunidad->partes()->with('propietarios')->orderBy('codigo')->chunk(200, function ($partes) use ($comunidad, $output): void {
                foreach ($partes as $parte) {
                    foreach ($parte->propietarios as $propietario) {
                        fputcsv($output, [$comunidad->codigo, $comunidad->nombre, $parte->codigo, $parte->descripcion, $parte->coeficiente_general, $propietario->nombre, $propietario->nif, $propietario->emails, $propietario->direccion, $propietario->codigo_postal, $propietario->poblacion, $propietario->provincia, $propietario->telefono, $propietario->movil], ';');
                    }
                }
            });
            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(
        ImportComunidadDataRequest $request,
        Comunidad $comunidad,
        ImportComunidadData $importComunidadData,
    ): RedirectResponse {
        Gate::authorize('update', $comunidad);
        $importComunidadData->handle($comunidad, $request->file('archivo'));

        $this->flashSuccess('Datos importados correctamente.');

        return back();
    }
}
