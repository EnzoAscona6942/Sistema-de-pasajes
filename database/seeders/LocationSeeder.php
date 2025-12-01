<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Lista de Localidades Reales de Formosa y la región
        $realLocations = [
            // Capital y Nodos Principales
            ['name' => 'Terminal de Ómnibus de Formosa', 'city' => 'Formosa', 'province' => 'Formosa'],
            ['name' => 'Terminal de Clorinda', 'city' => 'Clorinda', 'province' => 'Formosa'],
            ['name' => 'Terminal de Pirané', 'city' => 'Pirané', 'province' => 'Formosa'],
            ['name' => 'Terminal de El Colorado', 'city' => 'El Colorado', 'province' => 'Formosa'],
            ['name' => 'Terminal de Las Lomitas', 'city' => 'Las Lomitas', 'province' => 'Formosa'],
            
            // Interior de Formosa
            ['name' => 'Terminal Mayor Vicente Villafañe', 'city' => 'Mayor Vicente Villafañe', 'province' => 'Formosa'],
            ['name' => 'Terminal de Ingeniero Juárez', 'city' => 'Ingeniero Juárez', 'province' => 'Formosa'],
            ['name' => 'Terminal de Laguna Blanca', 'city' => 'Laguna Blanca', 'province' => 'Formosa'],
            ['name' => 'Terminal de Ibarreta', 'city' => 'Ibarreta', 'province' => 'Formosa'],
            ['name' => 'Terminal de Palo Santo', 'city' => 'Palo Santo', 'province' => 'Formosa'],
            ['name' => 'Terminal de Comandante Fontana', 'city' => 'Comandante Fontana', 'province' => 'Formosa'],
            ['name' => 'Terminal de General Güemes', 'city' => 'General Güemes', 'province' => 'Formosa'],
            ['name' => 'Terminal de Estanislao del Campo', 'city' => 'Estanislao del Campo', 'province' => 'Formosa'],
            ['name' => 'Terminal de Pozo del Tigre', 'city' => 'Pozo del Tigre', 'province' => 'Formosa'],
            ['name' => 'Terminal de Riacho He Hé', 'city' => 'Riacho He Hé', 'province' => 'Formosa'],
            ['name' => 'Terminal de Laguna Naineck', 'city' => 'Laguna Naineck', 'province' => 'Formosa'],
            ['name' => 'Terminal de Herradura', 'city' => 'Herradura', 'province' => 'Formosa'],
            ['name' => 'Terminal de Misión Tacaaglé', 'city' => 'Misión Tacaaglé', 'province' => 'Formosa'],
            ['name' => 'Terminal de El Espinillo', 'city' => 'El Espinillo', 'province' => 'Formosa'],
            ['name' => 'Terminal de Villa Dos Trece', 'city' => 'Villa Dos Trece', 'province' => 'Formosa'],
            ['name' => 'Terminal de General Mansilla', 'city' => 'General Mansilla', 'province' => 'Formosa'],
            ['name' => 'Parada Villa Escolar', 'city' => 'Villa Escolar', 'province' => 'Formosa'],
            ['name' => 'Terminal de Gran Guardia', 'city' => 'Gran Guardia', 'province' => 'Formosa'],
            ['name' => 'Terminal de Subteniente Perín', 'city' => 'Subteniente Perín', 'province' => 'Formosa'],

            // Conexiones Regionales (Para que haya viajes fuera de la provincia también)
            ['name' => 'Terminal de Eldorado', 'city' => 'Eldorado', 'province' => 'Misiones'],
            ['name' => 'Terminal de Resistencia', 'city' => 'Resistencia', 'province' => 'Chaco'],
            ['name' => 'Terminal de Corrientes', 'city' => 'Corrientes', 'province' => 'Corrientes'],
            ['name' => 'Terminal de Asunción', 'city' => 'Asunción', 'province' => 'Paraguay'], // Internacional cercano
        ];

        // 1. Insertar las ubicaciones reales
        foreach ($realLocations as $loc) {
            // Usamos firstOrCreate para evitar duplicados si corres el seeder dos veces sin fresh
            Location::firstOrCreate(
                ['name' => $loc['name'], 'city' => $loc['city']], 
                $loc
            );
        }

        // 2. Rellenar hasta llegar a 50 (si faltan) con datos genéricos pero consistentes
        $currentCount = Location::count();
        $needed = 40 - $currentCount;

        if ($needed > 0) {
            // Un array de pueblos ficticios o menores para rellenar
            $pueblosRelleno = ['Mojón de Fierro', 'Boca Riacho Pilagá', 'San Hilario', 'Mariano Boedo', 'Pastoril'];
            
            for ($i = 0; $i < $needed; $i++) {
                $nombrePueblo = $pueblosRelleno[$i % count($pueblosRelleno)] . ' ' . ($i + 1);
                
                Location::create([
                    'name' => "Parada $nombrePueblo",
                    'city' => $nombrePueblo,
                    'province' => 'Formosa'
                ]);
            }
        }
    }
}