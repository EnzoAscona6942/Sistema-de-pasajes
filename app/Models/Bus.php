<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\ServiceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Bus extends Model
{
    use HasFactory;
    protected $fillable = [
        'company_id', 
        'plate_number', 
        'model', 
        'capacity', 
        'service_type'
    ];

    protected $casts = [
        'service_type' => ServiceType::class,
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class);
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }
}