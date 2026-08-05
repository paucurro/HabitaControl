<?php

namespace Database\Factories;

use App\Models\Comunicado;
use App\Models\Comunidad;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comunicado>
 */
class ComunicadoFactory extends Factory
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
            'creado_por_user_id' => User::factory(),
            'asunto' => fake()->sentence(),
            'contenido' => fake()->paragraph(),
            'estado' => 'borrador',
        ];
    }
}
