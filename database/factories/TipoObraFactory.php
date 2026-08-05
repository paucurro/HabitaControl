<?php

namespace Database\Factories;

use App\Models\Comunidad;
use App\Models\TipoObra;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TipoObra>
 */
class TipoObraFactory extends Factory
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
            'codigo' => fake()->unique()->bothify('OB-###'),
            'descripcion' => fake()->sentence(3),
        ];
    }
}
