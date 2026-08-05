<?php

namespace Database\Factories;

use App\Models\Comunidad;
use App\Models\Parte;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Parte>
 */
class ParteFactory extends Factory
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
            'codigo' => fake()->unique()->bothify('P-###'),
            'descripcion' => fake()->randomElement(['Vivienda', 'Local', 'Garaje']),
            'coeficiente_general' => fake()->randomFloat(8, 0, 100),
        ];
    }
}
