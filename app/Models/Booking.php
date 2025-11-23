<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\BookingStatus;

class Booking extends Model
{
    use HasUlids;

    protected $fillable = [
        'user_id', 
        'trip_id', 
        'seat_id', 
        'status', 
        'payment_id', 
        'price_paid'
    ];

    protected $casts = [
        'status' => BookingStatus::class,
        'price_paid' => 'decimal:2'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function seat(): BelongsTo
    {
        return $this->belongsTo(Seat::class);
    }
}