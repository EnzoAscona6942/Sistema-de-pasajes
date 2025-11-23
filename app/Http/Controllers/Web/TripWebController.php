<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\Location;
use App\Enums\BookingStatus;
use App\Enums\ServiceType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TripWebController extends Controller
{
    /**
     * Página de Inicio: Muestra el buscador con las localidades.
     */
    public function home(): Response
    {
        // Enviamos solo lo necesario para el select (ID y Nombre)
        $locations = Location::select('id', 'name', 'city')
            ->orderBy('city')
            ->get()
            ->map(fn($loc) => [
                'id' => $loc->id,
                'name' => "{$loc->city} ({$loc->name})"
            ]);

        return Inertia::render('Home', [
            'locations' => $locations
        ]);
    }

    /**
     * Resultados de Búsqueda: Lista los viajes disponibles.
     */
    public function index(Request $request): Response
    {
        $request->validate([
            'origin_id' => 'nullable|exists:locations,id',
            'destination_id' => 'nullable|exists:locations,id',
            'date' => 'nullable|date',
        ]);

        $query = Trip::query()
            ->with(['bus.company', 'origin', 'destination']);

        // Filtros dinámicos
        if ($request->filled('origin_id')) {
            $query->where('origin_id', $request->input('origin_id'));
        }
        if ($request->filled('destination_id')) {
            $query->where('destination_id', $request->input('destination_id'));
        }
        if ($request->filled('date')) {
            $query->whereDate('departure_time', $request->input('date'));
        }

        // Ordenar por hora de salida
        $trips = $query->orderBy('departure_time')->get()->map(function ($trip) {
            return [
                'id' => $trip->id,
                'origin' => $trip->origin->city,
                'destination' => $trip->destination->city,
                'company_name' => $trip->bus->company->name,
                'service_type' => $trip->bus->service_type->label(),
                'departure_time_formatted' => $trip->departure_time->format('H:i'),
                'arrival_time_formatted' => $trip->arrival_time_estimated->format('H:i'),
                // Duración legible "5h 30m"
                'duration' => $trip->departure_time->diff($trip->arrival_time_estimated)->format('%hh %Im'),
                'price_preview' => number_format($trip->calculated_price, 2, ',', '.'),
            ];
        });

        return Inertia::render('Trips/Index', [
            'trips' => $trips,
            'filters' => $request->all() // Para mantener el estado del buscador si quieres
        ]);
    }

    /**
     * Selección de Asientos: Detalle del viaje y grilla visual.
     */
    public function show(string $id): Response
    {
        // Cargamos el viaje con el Bus, sus Asientos y las Reservas activas
        $trip = Trip::with(['origin', 'destination', 'bus.seats', 'bookings'])
            ->findOrFail($id);

        // 1. Identificar asientos ocupados (excluyendo cancelados)
        $occupiedSeatIds = $trip->bookings
            ->where('status', '!=', BookingStatus::CANCELLED)
            ->pluck('seat_id')
            ->toArray();

        // 2. Mapear asientos con lógica de precios y estado
        $seats = $trip->bus->seats->map(function ($seat) use ($trip, $occupiedSeatIds) {
            $isOccupied = in_array($seat->id, $occupiedSeatIds);
            
            // Determinar tipo (si el asiento tiene override o usa el del bus)
            $typeEnum = $seat->type ? ServiceType::tryFrom($seat->type) : $trip->bus->service_type;
            
            // Precio dinámico: Base * Multiplicador
            $price = $trip->base_price * ($typeEnum?->multiplier() ?? 1);

            return [
                'seat_id' => $seat->id,
                'number' => $seat->seat_number,
                'floor' => $seat->floor,
                'type' => $typeEnum?->label() ?? 'Estándar',
                'status' => $isOccupied ? 'occupied' : 'available',
                'price' => $price, // Enviamos como número para sumar en JS
            ];
        });

        // 3. Agrupar por piso para que el Frontend lo dibuje fácil
        $seatsByFloor = $seats->groupBy('floor');

        return Inertia::render('Trips/Show', [
            'trip' => [
                'id' => $trip->id,
                'origin' => $trip->origin, // Objeto completo location
                'destination' => $trip->destination,
                'base_price' => $trip->base_price,
            ],
            'seatsByFloor' => $seatsByFloor
        ]);
    }
}