<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $types = ['Laptop', 'Desktop', 'Écran', 'Accessoire', 'Tablette', 'Imprimante'];
        return [
            'name' => $this->faker->unique()->randomElement($types),
        ];
    }
}