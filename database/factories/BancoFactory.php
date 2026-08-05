<?php

namespace Database\Factories;

use App\Models\Banco;
use App\Models\Comunidad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Banco>
 */
class BancoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'comunidad_id' => Comunidad::factory(),
            'nombre' => fake()->company(),
            'direccion' => fake()->streetAddress(),
            'codigo_postal' => fake()->postcode(),
            'poblacion' => fake()->city(),
            'provincia' => fake()->city(),
            'telefonos' => fake()->phoneNumber(),
            'iban' => fake()->iban('ES'),
            'bic' => fake()->swiftBicNumber(),
            'codigo_interno' => fake()->unique()->numerify('######'),
            'es_principal' => false,
        ];
    }
}
