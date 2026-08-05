<?php

namespace Database\Factories;

use App\Models\Coeficiente;
use App\Models\Comunidad;
use App\Models\Parte;
use App\Models\TipoGasto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coeficiente>
 */
class CoeficienteFactory extends Factory
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
            'parte_id' => Parte::factory(),
            'tipo_gasto_id' => TipoGasto::factory(),
            'porcentaje' => fake()->randomFloat(8, 0, 100),
        ];
    }
}
