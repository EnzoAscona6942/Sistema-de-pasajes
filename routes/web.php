<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Web\TripWebController;
use App\Http\Controllers\Api\BookingController;

Route::get('/', [TripWebController::class, 'home'])->name('home');
Route::get('/trips', [TripWebController::class, 'index'])->name('trips.index');
// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });
Route::middleware(['auth'])->group(function () {
    // Al ponerla aquí, comparte la sesión de Inertia. ¡Adiós error 401!
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    
    // ... otras rutas auth
});
Route::get('/trips/{trip}', [TripWebController::class, 'show'])->name('trips.show');
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
