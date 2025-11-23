<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Trip;
use App\Models\Seat;
use App\Models\Booking;
use App\Enums\BookingStatus;
use App\Providers\PaymentService;
use Illuminate\Http\JsonResponse;
use App\Events\SeatBooked;

class BookingController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function store(Request $request): JsonResponse
    {
        // 1. Validación de Entrada
        $request->validate([
            'trip_id' => 'required|ulid|exists:trips,id',
            'seat_id' => 'required|exists:seats,id',
            // En producción, el user_id suele venir de Auth::id(), pero lo dejo flexible según tu pedido
            'user_id' => 'required|exists:users,id', 
        ]);

        try {
            // 2. INICIO DE LA ZONA CRÍTICA (Transacción DB)
            // Todo lo que ocurra aquí dentro es atómico.
            $booking = DB::transaction(function () use ($request) {
                
                // A. BLOQUEO PESIMISTA (Pessimistic Locking)
                // "SELECT * FROM seats WHERE id = ? FOR UPDATE"
                // Esto bloquea la fila del asiento. Nadie más puede escribir ni bloquear 
                // este asiento específico hasta que termine esta transacción.
                $seat = Seat::where('id', $request->seat_id)->lockForUpdate()->first();
                
                $trip = Trip::findOrFail($request->trip_id);
                SeatBooked::dispatch($trip->id, $seat->id);
                // B. Validación de Integridad Lógica
                // ¿El asiento pertenece al colectivo que hace este viaje?
                if ($seat->bus_id !== $trip->bus_id) {
                    throw new \Exception('El asiento no corresponde al colectivo de este viaje.');
                }

                // C. Verificación de Disponibilidad
                // Buscamos si YA existe una reserva activa (Pending o Confirmed)
                // para este viaje y este asiento.
                $isTaken = Booking::where('trip_id', $trip->id)
                    ->where('seat_id', $seat->id)
                    ->where('status', '!=', BookingStatus::CANCELLED) // Ignoramos las canceladas
                    ->exists();

                if ($isTaken) {
                    throw new \Exception('El asiento ya no está disponible.'); // Race condition evitada
                }

                // D. Cálculo del Precio (Instantánea)
                $finalPrice = $trip->base_price * $trip->bus->service_type->multiplier();
                // Nota: Si el asiento tuviera un tipo específico, la lógica sería más compleja aquí.

                // E. Creación de la Reserva (Estado: PENDING)
                return Booking::create([
                    'user_id'    => $request->user_id,
                    'trip_id'    => $trip->id,
                    'seat_id'    => $seat->id,
                    'status'     => BookingStatus::PENDING,
                    'price_paid' => $finalPrice,
                    'payment_id' => null, // Aún no pagó
                ]);
            });

            // 3. PROCESO DE PAGO (Fuera del bloqueo de BD)
            // Es buena práctica NO bloquear la base de datos mientras esperamos 
            // la respuesta de una API externa (puede tardar 2-3 segundos).
            
            $paymentSuccess = $this->paymentService->process($booking);

            if ($paymentSuccess) {
                // Actualizamos a Confirmado
                $booking->update([
                    'status' => BookingStatus::CONFIRMED,
                    'payment_id' => 'PAY-' . strtoupper(uniqid()), // ID Simulado
                ]);

                return response()->json([
                    'message' => 'Reserva confirmada exitosamente.',
                    'ticket_id' => $booking->id,
                    'status' => 'confirmed'
                ], 201);
            } else {
                // Falló el pago: Cancelamos la reserva para liberar el asiento
                $booking->update(['status' => BookingStatus::CANCELLED]);
                
                return response()->json([
                    'message' => 'El pago fue rechazado.',
                    'status' => 'cancelled'
                ], 402); // 402 Payment Required
            }

        } catch (\Exception $e) {
            // Manejo de errores (ej: asiento ocupado o validación lógica)
            return response()->json([
                'error' => 'No se pudo completar la reserva.',
                'message' => $e->getMessage()
            ], 409); // 409 Conflict
        }
    }
}