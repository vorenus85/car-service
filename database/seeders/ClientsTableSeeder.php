<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\Client;

class ClientsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('clients')->delete();
        
        $json = File::get("database/data/clients.json");
        $clients = json_decode($json, true); // Decode to associative array
        
        foreach ($clients as $client) {
            Client::create(array(
                'id' => $client['id'],
                'name' => $client['name'], 
                'idcard' => $client['idcard'],
            ));
        }

        $this->command->info('Clients imported successfully!');
    }
}