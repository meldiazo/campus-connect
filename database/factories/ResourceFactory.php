<?php

namespace Database\Factories;

use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResourceFactory extends Factory
{
    protected $model = Resource::class;

    public function definition(): array
    {
        return ['name' => fake()->words(3, true), 'type' => fake()->randomElement(['Equipo', 'Espacio', 'Mobiliario']), 'location' => fake()->streetAddress(), 'status' => 'Disponible', 'description' => fake()->sentence()];
    }
}
