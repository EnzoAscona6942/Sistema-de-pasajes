<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Enums\BookingStatus;
use App\Enums\ServiceType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Events\SeatSelecting;

class SeatController extends Controller
{
    // CAMBIO CLAVE: Recibimos el Modelo 'Trip' directamente, no un string
    public function index(Trip $trip): JsonResponse
    {
        // Cargar relaciones necesarias (bus y asientos) si no vienen cargadas
        $trip->load(['bus.seats', 'bookings', 'origin', 'destination']);

        // 2. IDs ocupados
        $occupiedSeatIds = $trip->bookings
            ->filter(fn ($booking) => $booking->status !== BookingStatus::CANCELLED)
            ->pluck('seat_id')
            ->toArray();

        // 3. Procesar asientos (con ordenamiento numérico)
        $seats = $trip->bus->seats
            ->sortBy(fn($seat) => (int) $seat->seat_number) // Ordenar "1", "2", "10"
            ->values()
            ->map(function ($seat) use ($trip, $occupiedSeatIds) {
                
                $isOccupied = in_array($seat->id, $occupiedSeatIds);
                
                // Lógica de tipo y precio...
                $seatTypeString = $seat->type ?? $trip->bus->service_type->value;
                $serviceEnum = ServiceType::tryFrom($seatTypeString) ?? ServiceType::SEMI_CAMA;
                $finalPrice = $trip->base_price * $serviceEnum->multiplier();

                return [
                    'seat_id' => $seat->id,
                    'number' => $seat->seat_number,
                    'floor' => $seat->floor,
                    'type' => $serviceEnum->label(),
                    'status' => $isOccupied ? 'occupied' : 'available',
                    'price' => round($finalPrice, 2),
                ];
            });

        return response()->json([
            'trip_info' => [
                'origin' => $trip->origin->name,
                'destination' => $trip->destination->name,
                'date' => $trip->departure_time->format('d/m/Y H:i'),
                'bus_model' => $trip->bus->model ?? 'Bus Standard', // Fallback por si es null
            ],
            // Forzamos que sea un objeto JSON vacío si no hay asientos, no null
            'seats_by_floor' => $seats->groupBy('floor') ?: (object)[] 
        ]);
    }
    public function toggleSelection(Request $request, $tripId)
    {
        $request->validate([
            'seat_id' => 'required',
            'action' => 'required|in:select,deselect'
        ]);

        $isSelecting = $request->action === 'select';

        // Disparamos el evento al WebSocket (No guardamos en BD)
        SeatSelecting::dispatch($tripId, $request->seat_id, $isSelecting);

        return response()->json(['status' => 'ok']);
    }
}