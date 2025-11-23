<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Location extends Model
{ 
    use HasFactory;
    protected $fillable = ['name', 'city', 'province'];

    // Viajes que salen de esta ubicación
    public function departures(): HasMany
    {
        return $this->hasMany(Trip::class, 'origin_id');
    }

    // Viajes que llegan a esta ubicación
    public function arrivals(): HasMany
    {
        return $this->hasMany(Trip::class, 'destination_id');
    }
}