<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    public function definition(): array
    {
        $city = $this->faker->city();
        
        return [
            'name' => 'Terminal de ' . $city,
            'city' => $city,
            'province' => $this->faker->state(),
        ];
    }
}