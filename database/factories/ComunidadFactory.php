<?php

namespace Database\Factories;

use App\Models\Comunidad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comunidad>
 */
class ComunidadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'codigo' => fake()->unique()->bothify('COM-###'),
            'nombre' => fake()->company(),
            'direccion' => fake()->streetAddress(),
            'codigo_postal' => fake()->postcode(),
            'poblacion' => fake()->city(),
            'provincia' => fake()->city(),
            'pais' => 'España',
        ];
    }
}
