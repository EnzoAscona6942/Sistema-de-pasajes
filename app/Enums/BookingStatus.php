<?php

namespace App\Enums;

enum BookingStatus: string
{
    case PENDING = 'pending';     // Reservado temporalmente, esperando pago
    case CONFIRMED = 'confirmed'; // Pago exitoso
    case CANCELLED = 'cancelled'; // Tiempo expirado o cancelado por usuario

    public function color(): string
    {
        // Útil para componentes de UI (ej: Badges de Tailwind)
        return match($this) {
            self::PENDING => 'yellow',
            self::CONFIRMED => 'green',
            self::CANCELLED => 'red',
        };
    }
}