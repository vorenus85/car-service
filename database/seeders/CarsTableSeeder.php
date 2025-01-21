<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\Car;

class CarsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('cars')->delete();
        
        $json = File::get("database/data/cars.json");
        $cars = json_decode($json, true);

        foreach ($cars as $car) {
            Car::create(array(
                'client_id' => $car['client_id'],
                'car_id' => $car['car_id'],
                'type' => $car['type'],
                'registered' => $car['registered'],
                'ownbrand' => $car['ownbrand'],
                'accident' => $car['accident'],
            ));
        }

        $this->command->info('Cars imported successfully!');
    }
}