<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function getClients(Request $request)
    {
        // Fetch query parameters with defaults
        $limit = $request->input('limit', 10); // Default limit is 10
        $page = $request->input('page', 1);    // Default page is 1

        // Query the clients table
        $query = Client::query();

        // Paginate results
        $clients = $query->paginate($limit, ['*'], 'page', $page);

        // Return paginated JSON response
        return response()->json($clients);
    }

    public function filterClients(Request $request)
    {
        // Fetch query parameters
        $clientName = $request->input('clientName');
        $clientIdCard = $request->input('clientIdCard');
        $errorMessage = null;
        $errorCode = 200; // Default to success
        $errorDescription = null;

        // Validations
        if ($clientName && $clientIdCard) {
            $errorMessage = "Search only Client name or Client ID card number but not both!";
            $errorDescription = "clientName: " . $clientName . " clientIdCard: " . $clientIdCard;
            $errorCode = 400; // Bad Request
        }

        if (is_null($clientName) && is_null($clientIdCard)) {
            $errorMessage = "Please provide either a Client name or Client ID card number!";
            $errorDescription = "clientName: " . $clientName . " clientIdCard: " . $clientIdCard;
            $errorCode = 400; // Bad Request
        }

        if ($clientIdCard && !is_numeric($clientIdCard)) {
            $errorMessage = "Client ID card number must be a numeric value!";
            $errorDescription = "clientName: " . $clientName . " clientIdCard: " . $clientIdCard;
            $errorCode = 400; // Bad Request
        }

        if ($errorMessage) {
            $error = [
                "status" => $errorCode,
                "body" => [
                    "error" => [
                        "description" => $errorDescription,
                        "message" => $errorMessage,
                    ],
                ],
            ];
            return response()->json($error, $errorCode);
        }

        // Query the clients table
        $query = Client::query();

        // Add filters based on query parameters
        if ($clientName) {
            $query->where('name', 'like', '%' . $clientName . '%');
        }

        if ($clientIdCard) {
            $query->where('idcard', $clientIdCard);
        }

        // Fetch all matching results
        $clients = $query->get();

        // Check if multiple results exist
        if ($clients->count() > 1) {
            $errorMessage = "There are multiple results for the search; please refine the parameters.";
            $errorDescription = "clientName: " . $clientName . " clientIdCard: " . $clientIdCard;
            $errorCode = 400; // Bad Request
            $error = [
                "status" => $errorCode,
                "body" => [
                    "error" => [
                        "description" => $errorDescription,
                        "message" => $errorMessage,
                    ],
                ],
            ];
            return response()->json($error, $errorCode);
        }

        // If only one client is found, fetch additional data
        if ($clients->count() === 1) {
            $client = $clients->first();

            $carCount = $client->cars()->count();


            $serviceLogCount = $client->serviceLogs()->count(); // Assuming a `serviceLogs` relationship exists in the Client model

            // Attach these counts to the response
            $response = [
                'data' => [$client],
                'carCount' => $carCount,
                'serviceLogCount' => $serviceLogCount,
            ];

            return response()->json($response);
        }

        // Return JSON response with all results
        return response()->json($clients);
    }
}
