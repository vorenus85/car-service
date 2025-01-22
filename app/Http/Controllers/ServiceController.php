<?php

namespace App\Http\Controllers;

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

        $query = Service::query()->where('client_id', $clientId)->where('car_id', $carId)->orderBy('lognumber', "desc");

        $services = $query->get();
        return response()->json($services);
    }
}
