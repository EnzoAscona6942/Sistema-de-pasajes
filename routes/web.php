<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\TripWebController;
use App\Http\Controllers\Api\BookingController; // Reusamos el de API para guardar

// Rutas Públicas de Inertia
Route::get('/', [TripWebController::class, 'home'])->name('home');
Route::get('/trips', [TripWebController::class, 'index'])->name('trips.index');
Route::get('/trips/{trip}', [TripWebController::class, 'show'])->name('trips.show');

// Rutas Protegidas (Para comprar)
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Usamos el controlador que creamos en la fase anterior.
    // Nota: Como es una llamada AJAX desde Inertia, la ruta API o Web funcionan,
    // pero por convención en Inertia solemos ponerlo aquí si no es una API pública externa.
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    
    // Panel de usuario (Dashboard)
    // Route::get('/dashboard', function () {
    //     return Inertia\Inertia::render('Dashboard');
    // })->name('dashboard');
});