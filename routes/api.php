<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TripController;
use App\Http\Controllers\Api\SeatController;
use App\Http\Controllers\Api\BookingController;

Route::prefix('v1')->group(function () {
    // Búsqueda de viajes (público)
    Route::get('/trips/search', [TripController::class, 'search']);
    
    // Obtener asientos de un viaje específico (público)
    Route::get('/trips/{trip}/seats', [SeatController::class, 'index']);
});
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/bookings', [BookingController::class, 'store']);
