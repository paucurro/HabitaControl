<?php

namespace Database\Factories;

use App\Models\Propietario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Propietario>
 */
class PropietarioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->name(),
            'nif' => fake()->unique()->bothify('########?'),
            'direccion' => fake()->streetAddress(),
            'codigo_postal' => fake()->postcode(),
            'poblacion' => fake()->city(),
            'emails' => fake()->safeEmail(),
            'enviar_email' => true,
        ];
    }
}
