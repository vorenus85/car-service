<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Http\Request;

class CarController extends Controller
{

    public function getCarsByClientId(Request $request)
    {
        $clientId = $request->input('clientId');

        // Query the cars table
        $query = Car::query();

        // Add filters based on query parameters
        if ($clientId) {
            $query->where('client_id',  $clientId);
        }

        // Fetch all matching results
        $cars = $query->get();

        // Return JSON response with all results
        return response()->json($cars);
    }
}
