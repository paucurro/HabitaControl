<?php

namespace App\Actions;

use App\Models\Comunidad;
use App\Models\Parte;
use App\Models\Propietario;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ImportComunidadData
{
    public function handle(Comunidad $comunidad, UploadedFile $archivo): void
    {
        $handle = fopen($archivo->getRealPath(), 'r');

        if ($handle === false) {
            throw ValidationException::withMessages(['archivo' => 'No se ha podido leer el archivo.']);
        }

        try {
            $header = array_map(
                fn (?string $value): string => trim((string) $value, "\xEF\xBB\xBF \t\n\r\0\x0B"),
                fgetcsv($handle, 0, ';') ?: [],
            );

            if (array_diff(['parte_codigo', 'propietario_nombre'], $header) !== []) {
                throw ValidationException::withMessages([
                    'archivo' => 'El CSV debe incluir parte_codigo y propietario_nombre.',
                ]);
            }

            DB::transaction(function () use ($handle, $header, $comunidad): void {
                while (($values = fgetcsv($handle, 0, ';')) !== false) {
                    $values = array_pad($values, count($header), null);
                    $row = array_combine($header, array_slice($values, 0, count($header)));

                    if (blank(Arr::get($row, 'parte_codigo')) || blank(Arr::get($row, 'propietario_nombre'))) {
                        continue;
                    }

                    $parte = Parte::updateOrCreate(
                        [
                            'comunidad_id' => $comunidad->id,
                            'codigo' => trim((string) Arr::get($row, 'parte_codigo')),
                        ],
                        [
                            'descripcion' => Arr::get($row, 'parte_descripcion') ?: null,
                            'coeficiente_general' => Arr::get($row, 'coeficiente') ?: 0,
                        ],
                    );
                    $propietario = $this->findOrCreatePropietario($row);
                    $parte->propietarios()->syncWithoutDetachingOrFail([$propietario->id]);
                }
            });
        } finally {
            fclose($handle);
        }
    }

    /** @param array<string, string|null> $row */
    private function findOrCreatePropietario(array $row): Propietario
    {
        $nif = Arr::get($row, 'propietario_nif');
        $propietario = filled($nif)
            ? Propietario::query()->where('nif', trim((string) $nif))->first()
            : null;

        return $propietario ?? Propietario::create([
            'nombre' => trim((string) Arr::get($row, 'propietario_nombre')),
            'nif' => $nif ?: null,
            'emails' => Arr::get($row, 'email') ?: null,
            'direccion' => Arr::get($row, 'direccion') ?: null,
            'codigo_postal' => Arr::get($row, 'codigo_postal') ?: null,
            'poblacion' => Arr::get($row, 'poblacion') ?: null,
            'provincia' => Arr::get($row, 'provincia') ?: null,
            'telefono' => Arr::get($row, 'telefono') ?: null,
            'movil' => Arr::get($row, 'movil') ?: null,
        ]);
    }
}
