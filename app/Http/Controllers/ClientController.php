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
        $page = $request->input('page', 0);    // Default page is 1

        // Query the clients table
        $query = Client::query();

        // Paginate results
        $clients = $query->paginate($limit, ['*'], 'page', $page);

        // Return paginated JSON response
        return response()->json($clients);
    }

    public function filterClients(Request $request)
    {
        // Fetch query parameters with defaults
        $limit = $request->input('limit', 10); // Default limit is 10
        $page = $request->input('page', 1);    // Default page is 1
        $clientName = $request->input('clientName');
        $clientIdcard = $request->input('clientIdcard');
        $errorMessage = "";
        $errorCode = null;

        // Validations
        if ($clientName && $clientIdcard) {
            $errorMessage = "Search only Client name or Client ID card number but not both!";
            $errorCode = 400; // Bad Request
        }

        if (!$clientName && !$clientIdcard) {
            $errorMessage = "Please provide either a Client name or Client ID card number!";
            $errorCode = 400; // Bad Request
        }



        if ($errorMessage) {
            $error = [
                "status" => $errorCode,
                "body" => [
                    "error" => [
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

        if ($clientIdcard) {
            $query->where('idcard', $clientIdcard);
        }

        // Paginate results
        $clients = $query->paginate($limit, ['*'], 'page', $page);

        // Return paginated JSON response
        return response()->json($clients);
    }
}
