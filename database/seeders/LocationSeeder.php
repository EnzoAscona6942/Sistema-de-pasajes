<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        // Lista de Terminales Reales
        $realLocations = [
            ['name' => 'Terminal de Ómnibus de Retiro', 'city' => 'CABA', 'province' => 'Buenos Aires'],
            ['name' => 'Terminal Dellepiane', 'city' => 'CABA', 'province' => 'Buenos Aires'],
            ['name' => 'Terminal de Mendoza', 'city' => 'Guaymallén', 'province' => 'Mendoza'],
            ['name' => 'Terminal de Córdoba', 'city' => 'Córdoba', 'province' => 'Córdoba'],
            ['name' => 'Estación Rosario Mariano Moreno', 'city' => 'Rosario', 'province' => 'Santa Fe'],
            ['name' => 'Terminal de Mar del Plata', 'city' => 'Mar del Plata', 'province' => 'Buenos Aires'],
            ['name' => 'Terminal de Bariloche', 'city' => 'San Carlos de Bariloche', 'province' => 'Río Negro'],
            ['name' => 'Terminal de Salta', 'city' => 'Salta', 'province' => 'Salta'],
            ['name' => 'Terminal de Iguazú', 'city' => 'Puerto Iguazú', 'province' => 'Misiones'],
            ['name' => 'ETON (Terminal Neuquén)', 'city' => 'Neuquén', 'province' => 'Neuquén'],
            ['name' => 'Terminal San Nicolás', 'city' => 'San Nicolás', 'province' => 'Buenos Aires'],
            ['name' => 'Terminal de Bahía Blanca', 'city' => 'Bahía Blanca', 'province' => 'Buenos Aires'],
            ['name' => 'Terminal de Tucumán', 'city' => 'San Miguel de Tucumán', 'province' => 'Tucumán'],
            ['name' => 'Terminal de Corrientes', 'city' => 'Corrientes', 'province' => 'Corrientes'],
            ['name' => 'Terminal de Posadas', 'city' => 'Posadas', 'province' => 'Misiones'],
            ['name' => 'Terminal de San Juan', 'city' => 'San Juan', 'province' => 'San Juan'],
            ['name' => 'Terminal de San Luis', 'city' => 'San Luis', 'province' => 'San Luis'],
            ['name' => 'Terminal de Villa Carlos Paz', 'city' => 'Villa Carlos Paz', 'province' => 'Córdoba'],
            ['name' => 'Terminal de Pinamar', 'city' => 'Pinamar', 'province' => 'Buenos Aires'],
            ['name' => 'Terminal de Villa Gesell', 'city' => 'Villa Gesell', 'province' => 'Buenos Aires'],
        ];

        // 1. Insertar las reales
        foreach ($realLocations as $loc) {
            Location::firstOrCreate(['name' => $loc['name']], $loc);
        }

        // 2. Rellenar hasta llegar a 50 si faltan
        $currentCount = Location::count();
        $needed = 50 - $currentCount;

        if ($needed > 0) {
            Location::factory()->count($needed)->create();
        }
    }
}