<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Seat extends Model
{
    protected $fillable = ['bus_id', 'seat_number', 'floor', 'type'];

    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }
}