<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Bus;
use App\Models\Location;

class TripFactory extends Factory
{
    public function definition(): array
    {
        // Fecha de salida aleatoria en los próximos 30 días
        $departure = $this->faker->dateTimeBetween('now', '+30 days');
        
        // Clonamos la fecha para no modificar la original y sumamos horas
        // Duración del viaje: entre 2 y 20 horas
        $arrival = (clone $departure)->modify('+' . rand(2, 20) . ' hours');

        return [
            'origin_id' => Location::factory(),
            'destination_id' => Location::factory(),
            'bus_id' => Bus::factory(),
            
            'departure_time' => $departure,
            'arrival_time_estimated' => $arrival,
            
            // Precio base entre 5.000 y 60.000
            'base_price' => $this->faker->randomFloat(2, 5000, 60000),
        ];
    }
}