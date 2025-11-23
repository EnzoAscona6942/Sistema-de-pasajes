<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Bus;
use App\Models\Seat;

class BusSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear 5 Empresas
        $companies = Company::factory()->count(5)->create();

        // 2. Recorrer cada empresa para asignarle buses
        foreach ($companies as $company) {
            
            // Crear 10 buses por empresa (Total 50)
            $buses = Bus::factory()
                ->count(10)
                ->for($company) // Vincula el bus a esta empresa
                ->create();

            // 3. Generar asientos para CADA bus creado
            foreach ($buses as $bus) {
                $this->createSeatsForBus($bus);
            }
        }
    }

    /**
     * Lógica para llenar el colectivo de asientos
     */
    private function createSeatsForBus(Bus $bus): void
    {
        $seatsData = [];
        
        for ($i = 1; $i <= $bus->capacity; $i++) {
            
            // Lógica de Pisos:
            // Si el bus tiene más de 40 asientos, asumimos que es doble piso.
            // Asientos 1 al 12 abajo (Piso 1), el resto arriba (Piso 2).
            // Si es chico (ej 30), todo es Piso 1.
            $floor = 1;
            if ($bus->capacity > 40 && $i > 12) {
                $floor = 2;
            }

            $seatsData[] = [
                'bus_id'      => $bus->id,
                'seat_number' => (string) $i, // Ej: "1", "20"
                'floor'       => $floor,
                'type'        => $bus->service_type, // Hereda: cama o semi-cama
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }

        // Insertar en lote (Bulk Insert) para no hacer 2000 queries individuales
        Seat::insert($seatsData);
    }
}