<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Asset;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin
        User::factory()->create([
            'name' => 'Admin System',
            'email' => 'admin@nexa.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // 2. Création de catégories réalistes
        $categories = ['Ordinateurs Portables', 'Écrans', 'Périphériques', 'Réseau', 'Mobilier'];
        foreach ($categories as $cat) {
            Category::factory()->create(['name' => $cat]);
        }

        // 3. Création de 50 matériels aléatoires liés aux catégories existantes
        $cats = Category::all();
        Asset::factory(50)->recycle($cats)->create();
    }
}