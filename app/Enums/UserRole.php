<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case PASSENGER = 'passenger';
    
    // Método opcional útil para mostrar textos lindos en el frontend
    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrador',
            self::PASSENGER => 'Pasajero',
        };
    }
}