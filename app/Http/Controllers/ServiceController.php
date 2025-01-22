<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function getCarServicesByClientId(Request $request)
    {
        $clientId = $request->input('clientId');
        $carId = $request->input('carId');

        // Validate clientId
        if (!$clientId) {
            return response()->json(['error' => 'Client ID is required.'], 400);
        }

        // Validate carId
        if (!$carId) {
            return response()->json(['error' => 'Car ID is required.'], 400);
        }

        // Get the registered date of the car
        $registeredDate = Car::getRegisteredDate($clientId, $carId);

        // Fetch services and modify the `eventtime` if it is null
        $services = Service::query()
            ->where('client_id', $clientId)
            ->where('car_id', $carId)
            ->orderBy('lognumber', "desc")
            ->get()
            ->map(function ($service) use ($registeredDate) {
                if (is_null($service->eventtime)) {
                    $service->eventtime = $registeredDate;
                }
                return $service;
            });
        return response()->json($services);
    }
}
