<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Structure;

class StructureSeeder extends Seeder
{
    public function run()
    {
        $structures = [
            'Australe Formation',
            'Australe CEC',
            'Cab Gestion',
            'AMC Solutions',
            'CAP Avenir',
        ];

        foreach ($structures as $name) {
            Structure::firstOrCreate(['name' => $name]);
        }
    }
}