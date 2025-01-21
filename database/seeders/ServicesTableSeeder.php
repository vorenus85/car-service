<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\Service;

class ServicesTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('services')->delete();
        
        $json = File::get("database/data/services.json");
        $services = json_decode($json, true);

        foreach ($services as $service) {
            Service::create([
                'client_id' => $service['client_id'],
                'car_id' => $service['car_id'],
                'lognumber' => $service['lognumber'],
                'event' => $service['event'],
                'eventtime' => $service['eventtime'],
                'document_id' => $service['document_id'],
            ]);
        }

        $this->command->info('Services imported successfully!');
    }
}