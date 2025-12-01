<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Web\TripWebController;
use App\Http\Controllers\Api\BookingController;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Enums\BookingStatus;
use Illuminate\Support\Facades\DB;

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
Route::get('/dashboard', function (Request $request) {
    $user = $request->user();

    // 1. Obtener Historial de Pasajes (con relaciones necesarias)
    $bookings = Booking::where('user_id', $user->id)
        ->with(['trip.origin', 'trip.destination', 'trip.bus.company', 'seat'])
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(fn($b) => [
            'id' => $b->id,
            'origin' => $b->trip->origin->name,
            'destination' => $b->trip->destination->name,
            'date' => $b->trip->departure_time->format('d/m/Y H:i'),
            'price' => number_format($b->price_paid, 2, ',', '.'),
            'seat' => $b->seat->seat_number . ' (' . $b->seat->floor . '° Piso)',
            'company' => $b->trip->bus->company->name,
            'status' => $b->status,
            'qr_code' => 'https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=' . $b->id
        ]);

    // 2. Datos para el Gráfico: Viajes confirmados en los últimos 30 días agrupados por DESTINO
    $stats = Booking::where('user_id', $user->id)
        ->where('status', BookingStatus::CONFIRMED)
        // CAMBIO AQUÍ: Agregamos 'bookings.' antes de created_at
        ->where('bookings.created_at', '>=', now()->subDays(30)) 
        ->join('trips', 'bookings.trip_id', '=', 'trips.id')
        ->join('locations', 'trips.destination_id', '=', 'locations.id')
        ->select('locations.city', DB::raw('count(*) as total'))
        ->groupBy('locations.city')
        ->get();

    return Inertia::render('Dashboard', [
        'bookings' => $bookings,
        'chartData' => [
            'labels' => $stats->pluck('city'),
            'datasets' => [[
                'backgroundColor' => ['#1A237E', '#FF6F00', '#2E7D32', '#C62828', '#00ACC1'], // Colores de Vuetify
                'data' => $stats->pluck('total')
            ]]
        ]
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/auth.php';
