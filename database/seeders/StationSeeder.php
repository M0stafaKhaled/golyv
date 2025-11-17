<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stations = ['Cairo', 'Giza', 'Fayyum', 'Minya', 'Asyut'];

        foreach ($stations as $s) {
            \App\Models\Station::create(['name' => $s]);
        }
    }
}
