<?php

namespace Database\Factories;

use App\Models\Administracion;
use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Proveedor>
 */
class ProveedorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'administracion_id' => Administracion::factory(),
            'nombre' => fake()->company(),
            'nif' => fake()->bothify('B########'),
            'email' => fake()->companyEmail(),
        ];
    }
}
