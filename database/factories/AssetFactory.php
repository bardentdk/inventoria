<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true), // ex: Dell XPS 15
            'serial_number' => strtoupper($this->faker->bothify('SN-####-????')),
            'inventory_code' => 'NEXA-' . $this->faker->unique()->numberBetween(1000, 9999),
            'category_id' => Category::factory(), // Crée une caté par défaut si besoin
            'status' => $this->faker->randomElement(['available', 'assigned', 'broken', 'repair']),
            'specs' => $this->faker->sentence(),
        ];
    }
}