<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Trip;
use App\Models\Seat;
use App\Models\Booking;
use App\Enums\BookingStatus;
use App\Providers\PaymentService; // O App\Services\PaymentService según tu estructura
use Illuminate\Http\JsonResponse;
use App\Events\SeatBooked; 
use App\Jobs\SendTicketJob;

class BookingController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function store(Request $request): JsonResponse
    {

        // 1. Validamos SOLO los datos del viaje.
        // NO validamos user_id porque lo tomamos del sistema (Auth)
        $request->validate([
            'trip_id' => 'required|ulid|exists:trips,id',
            'seat_id' => 'required|exists:seats,id',
        ]);

        // 2. Obtenemos el usuario autenticado de forma segura
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        try {
            $booking = DB::transaction(function () use ($request, $user) {
                
                // BLOQUEO PESIMISTA
                $seat = Seat::where('id', $request->seat_id)->lockForUpdate()->first();
                $trip = Trip::findOrFail($request->trip_id);

                // Validar Integridad
                if ($seat->bus_id !== $trip->bus_id) {
                    throw new \Exception('El asiento no corresponde a este viaje.');
                }

                // Validar Disponibilidad
                $isTaken = Booking::where('trip_id', $trip->id)
                    ->where('seat_id', $seat->id)
                    ->where('status', '!=', BookingStatus::CANCELLED->value)
                    ->exists();

                if ($isTaken) {
                    throw new \Exception('El asiento acaba de ser ocupado por otro pasajero.');
                }

                // Precio
                $finalPrice = round($trip->base_price * $trip->bus->service_type->multiplier());

                // Crear Reserva (Usando el ID del usuario autenticado)
                return Booking::create([
                    'user_id'    => $user->id, // <--- SEGURIDAD AQUÍ
                    'trip_id'    => $trip->id,
                    'seat_id'    => $seat->id,
                    'status'     => BookingStatus::PENDING,
                    'price_paid' => $finalPrice,
                ]);
                // SeatBooked::dispatch($trip->id, $seat->id);
            });
            // Pago (Simulado)
            if ($this->paymentService->process($booking)) {
                $booking->update([
                    'status' => BookingStatus::CONFIRMED,
                    'payment_id' => 'PAY-' . strtoupper(uniqid()),
                ]);
                
                // Disparar evento de WebSocket (Opcional)
                // SeatBooked::dispatch($booking->trip_id, $booking->seat_id);
                SendTicketJob::dispatch($booking);
                return response()->json(['status' => 'success', 'message' => '¡Reserva exitosa!'], 201);
            } 
            
            // Fallo de pago
            $booking->update(['status' => BookingStatus::CANCELLED]);
            return response()->json(['status' => 'error', 'message' => 'Pago rechazado'], 402);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 409);
        }
    }
}