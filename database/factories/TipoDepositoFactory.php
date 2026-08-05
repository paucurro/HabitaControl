<?php

namespace Database\Factories;

use App\Models\Comunidad;
use App\Models\TipoDeposito;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TipoDeposito>
 */
class TipoDepositoFactory extends Factory
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
            'nombre' => fake()->randomElement(['Fondo de obras', 'Depósito inicial', 'Fianza']),
            'importe' => fake()->randomFloat(4, 0, 5000),
        ];
    }
}
