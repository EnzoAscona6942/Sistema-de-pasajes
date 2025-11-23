<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail; // Descomentar si requieres verificación
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\UserRole;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'dni',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class, // Cast automático al Enum
        ];
    }

    // Relación: Un usuario tiene muchas reservas
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    // Helper para verificar permisos rápidamente
    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }
}