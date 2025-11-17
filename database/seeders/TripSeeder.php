<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $trip = \App\Models\Trip::create([
            'name' => 'Cairo → Asyut',
            'start_station_id' => 1,
            'end_station_id' => 5
        ]);

        $order = 1;
        $stations = [1,2,3,4,5]; // Cairo → Giza → Fayyum → Minya → Asyut

        foreach ($stations as $s) {
            \App\Models\TripStation::create([
                'trip_id' => $trip->id,
                'station_id' => $s,
                'order' => $order++
            ]);
        }

        $bus = \App\Models\Bus::create([
            'trip_id' => $trip->id,
            'total_seats' => 12
        ]);

        for ($i = 1; $i <= 12; $i++) {
            \App\Models\BusSeat::create([
                'bus_id' => $bus->id,
                'seat_number' => $i
            ]);
        }
    }
}
