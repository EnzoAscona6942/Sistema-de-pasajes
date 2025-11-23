<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->company() . ' Transportes',
            // Genera algo tipo "20345678901"
            'legal_id' => $this->faker->unique()->numerify('20########1'),
        ];
    }
}