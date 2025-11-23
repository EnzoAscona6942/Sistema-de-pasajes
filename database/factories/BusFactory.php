<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Company; // Importante relacionar
use App\Enums\ServiceType;

class BusFactory extends Factory
{
    public function definition(): array
    {
        // Elegimos un tipo de servicio al azar
        $serviceType = $this->faker->randomElement(ServiceType::cases());

        // Lógica de negocio: Cama son buses más espaciosos (menos asientos)
        $capacity = match ($serviceType) {
            ServiceType::CAMA => 30,       // Ej: 3 filas de asientos anchos
            ServiceType::SEMI_CAMA => 50,  // Ej: 4 filas estándar
        };

        return [
            // Crea una empresa nueva si no se le pasa una
            'company_id' => Company::factory(),
            
            // Genera patente formato nuevo (AA123BB)
            'plate_number' => $this->faker->unique()->bothify('??###??'),
            
            'model' => $this->faker->randomElement([
                'Marcopolo Paradiso G7', 
                'Scania K440', 
                'Volvo B450R', 
                'Mercedes-Benz O500'
            ]),
            
            'capacity' => $capacity,
            'service_type' => $serviceType,
        ];
    }
}