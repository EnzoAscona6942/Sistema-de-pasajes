<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trip;
use App\Models\Bus;
use App\Models\Location;

class TripSeeder extends Seeder
{
    public function run(): void
    {
        // Traer datos existentes
        $locations = Location::all();
        $buses = Bus::all();

        // Validación básica
        if ($locations->count() < 2 || $buses->count() === 0) {
            $this->command->info('No hay suficientes ubicaciones o buses para crear viajes.');
            return;
        }

        // Generar 200 viajes aleatorios
        for ($i = 0; $i < 200; $i++) {
            
            // Seleccionar origen
            $origin = $locations->random();
            
            // Seleccionar destino (que no sea el mismo que el origen)
            $destination = $locations->where('id', '!=', $origin->id)->random();
            
            // Seleccionar un bus al azar
            $bus = $buses->random();

            // Crear el viaje usando el Factory para fechas y precios
            Trip::factory()->create([
                'origin_id'      => $origin->id,
                'destination_id' => $destination->id,
                'bus_id'         => $bus->id,
                // departure_time y base_price se generan en el factory
            ]);
        }
    }
}