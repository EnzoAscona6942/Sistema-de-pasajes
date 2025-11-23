<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Enums\BookingStatus;
use App\Enums\ServiceType;
use Illuminate\Http\JsonResponse;

class SeatController extends Controller
{
    public function index(string $tripId): JsonResponse
    {
        // 1. Buscamos el viaje con sus relaciones necesarias
        $trip = Trip::with(['bus.seats', 'bookings'])->findOrFail($tripId);

        // 2. Obtenemos los IDs de asientos que YA están reservados
        // Filtramos: ignoramos las canceladas, el resto (pending/confirmed) ocupan lugar.
        $occupiedSeatIds = $trip->bookings
            ->filter(fn ($booking) => $booking->status !== BookingStatus::CANCELLED)
            ->pluck('seat_id')
            ->toArray();

        // 3. Procesamos la lista de asientos del colectivo
        $seats = $trip->bus->seats->map(function ($seat) use ($trip, $occupiedSeatIds) {
            
            // A. Determinar Disponibilidad
            $isOccupied = in_array($seat->id, $occupiedSeatIds);

            // B. Determinar Tipo de Asiento
            // Si el asiento tiene un tipo específico (ej: Asiento cama en bus mixto), úsalo.
            // Si no, usa el tipo general del bus.
            $seatTypeString = $seat->type ?? $trip->bus->service_type->value;
            
            // Convertimos string a Enum para usar sus métodos
            $serviceEnum = ServiceType::tryFrom($seatTypeString) ?? ServiceType::SEMI_CAMA;

            // C. Calcular Precio Dinámico
            // Precio Base Trip * Multiplicador del Tipo de Asiento
            $finalPrice = $trip->base_price * $serviceEnum->multiplier();

            return [
                'seat_id' => $seat->id,
                'number' => $seat->seat_number,
                'floor' => $seat->floor, // Piso 1 o 2
                'type' => $serviceEnum->label(),
                'status' => $isOccupied ? 'occupied' : 'available',
                'price' => round($finalPrice, 2), // Redondeo a 2 decimales
                'currency' => 'ARS',
            ];
        });

        // 4. Agrupamos por piso para facilitar el frontend
        return response()->json([
            'trip_info' => [
                'origin' => $trip->origin->name,
                'destination' => $trip->destination->name,
                'date' => $trip->departure_time->format('d/m/Y H:i'),
                'bus_model' => $trip->bus->model,
            ],
            'seats_by_floor' => $seats->groupBy('floor')
        ]);
    }
}