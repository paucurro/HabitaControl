<?php

namespace Database\Factories;

use App\Models\Comunidad;
use App\Models\DiarioObra;
use App\Models\TipoObra;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiarioObra>
 */
class DiarioObraFactory extends Factory
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
            'tipo_obra_id' => TipoObra::factory(),
            'fecha' => fake()->date(),
            'numero_documento' => fake()->numerify('OB-####'),
            'descripcion' => fake()->sentence(),
            'debe' => fake()->randomFloat(4, 0, 1000),
            'haber' => 0,
        ];
    }
}
