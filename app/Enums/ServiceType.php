<?php

namespace App\Enums;

enum ServiceType: string
{
    case CAMA = 'cama';
    case SEMI_CAMA = 'semi-cama';
    
    // Lógica encapsulada: El precio depende del tipo
    public function multiplier(): float
    {
        return match($this) {
            self::CAMA => 1.5,      // 50% más caro
            self::SEMI_CAMA => 1.0, // Precio estándar
        };
    }

    public function label(): string
    {
        return match($this) {
            self::CAMA => 'Coche Cama (Ejecutivo)',
            self::SEMI_CAMA => 'Semi Cama (Estándar)',
        };
    }
}