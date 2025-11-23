<?php

namespace App\Providers;

use App\Models\Booking;

class PaymentService
{
    /**
     * Simula una transacción con una pasarela externa (Stripe/MercadoPago).
     */
    public function process(Booking $booking): bool
    {
        // Simulación: 90% de probabilidad de éxito, 10% de tarjeta rechazada
        // En la vida real, esto haría una llamada HTTP a la API del banco.
        return rand(1, 100) <= 90;
    }
}