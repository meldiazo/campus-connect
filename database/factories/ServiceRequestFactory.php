<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceRequestFactory extends Factory
{
    protected $model = ServiceRequest::class;

    public function definition(): array
    {
        return ['user_id' => User::factory(), 'category_id' => Category::factory(), 'title' => fake()->sentence(5), 'description' => fake()->paragraph(), 'location' => fake()->randomElement(['Edificio A', 'Biblioteca', 'Laboratorio 1']), 'priority' => fake()->randomElement(['Baja', 'Media', 'Alta', 'Urgente']), 'status' => 'Pendiente'];
    }
}
