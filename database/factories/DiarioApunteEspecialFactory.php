<?php

namespace Database\Factories;

use App\Models\Comunidad;
use App\Models\DiarioApunteEspecial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiarioApunteEspecial>
 */
class DiarioApunteEspecialFactory extends Factory
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
            'tipo' => 'extraordinario',
            'fecha' => fake()->date(),
            'descripcion' => fake()->sentence(),
            'importe' => fake()->randomFloat(4, -1000, 1000),
        ];
    }
}
