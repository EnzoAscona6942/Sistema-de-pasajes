<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TripController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        // 1. Validación simple (Podrías usar un FormRequest para más limpieza)
        $request->validate([
            'origin_id' => 'required|exists:locations,id',
            'destination_id' => 'required|exists:locations,id',
            'date' => 'required|date_format:Y-m-d', // Ej: 2024-12-31
        ]);

        // 2. Construcción de la consulta
        $trips = Trip::query()
            ->with(['bus.company', 'origin', 'destination']) // Cargar relaciones
            ->where('origin_id', $request->input('origin_id'))
            ->where('destination_id', $request->input('destination_id'))
            // Usamos whereDate para ignorar la hora exacta y buscar por día
            ->whereDate('departure_time', $request->input('date'))
            // Ordenar por hora de salida
            ->orderBy('departure_time', 'asc')
            ->get();

        // 3. Transformación de datos (Resource Layer simple)
        $data = $trips->map(function ($trip) {
            return [
                'trip_id' => $trip->id, // ULID
                'company' => $trip->bus->company->name,
                'service_type' => $trip->bus->service_type->label(), // "Cama", etc.
                'departure_time' => $trip->departure_time->format('H:i'),
                'arrival_time' => $trip->arrival_time_estimated->format('H:i'),
                'duration' => $trip->departure_time->diffForHumans($trip->arrival_time_estimated, true),
                // Precio "desde" (asumiendo el precio base x multiplicador del bus)
                'price_preview' => $trip->calculated_price, 
                'available_seats' => $trip->bus->capacity - $trip->bookings()->where('status', '!=', 'cancelled')->count(),
            ];
        });

        return response()->json(['data' => $data]);
    }
}