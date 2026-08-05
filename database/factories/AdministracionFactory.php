<?php

namespace Database\Factories;

use App\Models\Administracion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Administracion>
 */
class AdministracionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'propietario_user_id' => User::factory(),
            'nombre' => fake()->company(),
            'slug' => fake()->unique()->slug(),
            'activa' => true,
        ];
    }
}
