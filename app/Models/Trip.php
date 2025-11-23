<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Trip extends Model
{   use HasFactory;
    use HasUlids; // Genera automáticamente IDs tipo "01ARZ3NDEK..."

    protected $fillable = [
        'origin_id', 
        'destination_id', 
        'bus_id', 
        'departure_time', 
        'arrival_time_estimated', 
        'base_price'
    ];

    protected $casts = [
        'departure_time' => 'datetime',
        'arrival_time_estimated' => 'datetime',
        'base_price' => 'decimal:2'
    ];

    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }

    public function origin(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'origin_id');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'destination_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Calcula el precio final sugerido basado en el multiplicador del bus.
     * Ej: Precio Base 1000 * Bus Cama (1.5) = 1500
     */
    public function getCalculatedPriceAttribute(): float
    {
        return $this->base_price * $this->bus->service_type->multiplier();
    }
}