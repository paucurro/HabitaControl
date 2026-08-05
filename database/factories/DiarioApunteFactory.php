<?php

namespace Database\Factories;

use App\Models\DiarioApunte;
use App\Models\Comunidad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiarioApunte>
 */
class DiarioApunteFactory extends Factory
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
            'fecha' => fake()->date(),
            'numero_documento' => fake()->numerify('DOC-####'),
            'descripcion' => fake()->sentence(),
            'debe' => fake()->randomFloat(4, 0, 1000),
            'haber' => fake()->randomFloat(4, 0, 1000),
        ];
    }
}
