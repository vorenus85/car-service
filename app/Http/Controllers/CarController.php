<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Service;
use Illuminate\Http\Request;

class CarController extends Controller
{

    public function getCarsByClientId(Request $request)
    {
        $clientId = $request->input('clientId');

        // Validate clientId
        if (!$clientId) {
            return response()->json(['error' => 'Client ID is required.'], 400);
        }

        // Query the cars table and process results
        $cars = Car::where('client_id', $clientId)->get()->map(function ($car) use ($clientId) {
            $serviceLogs = Service::where('client_id', $clientId)->where('car_id', $car->car_id)->orderBy('lognumber', 'desc')->get();
            $latestLog = $serviceLogs->first();
            return [
                "client_id" => $clientId,
                "car_id" => $car->car_id,
                "type" => $car->type,
                "registered" => $car->registered,
                "ownbrand" => $car->ownbrand,
                "accident" => $car->accident,
                "latestLog" => $latestLog,
            ];
        });

        // Return JSON response with all results
        return response()->json($cars);
    }
}
