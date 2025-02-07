<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\ServiceController;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('CarServiceList');
});

Route::get('/api/clients', [ClientController::class, 'getClients']);

Route::get('/api/filterClients', [ClientController::class, 'filterClients']);

Route::get('/api/carsByClient', [CarController::class, 'getCarsByClientId']);

Route::get('/api/carServicesByClient', [ServiceController::class, 'getCarServicesByClientId']);
