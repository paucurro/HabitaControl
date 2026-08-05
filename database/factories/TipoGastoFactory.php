<?php

namespace Database\Factories;

use App\Models\Comunidad;
use App\Models\TipoGasto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TipoGasto>
 */
class TipoGastoFactory extends Factory
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
            'codigo' => fake()->unique()->bothify('G-###'),
            'descripcion' => fake()->randomElement(['Ascensor', 'Limpieza', 'Portería', 'Comunidad', 'Jardinería']),
        ];
    }
}
