<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Enums\UserRole;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Crear Usuarios Fijos para Login
        
        // Usuario Admin
        User::factory()->create([
            'name'     => 'Administrador',
            'email'    => 'admin@plataforma.com',
            'password' => 'password', // El factory lo hashea
            'dni'      => '11111111',
            'role'     => UserRole::ADMIN,
        ]);

        // Usuario Pasajero
        User::factory()->create([
            'name'     => 'Pasajero Cliente',
            'email'    => 'cliente@gmail.com',
            'password' => 'password',
            'dni'      => '22222222',
            'role'     => UserRole::PASSENGER,
        ]);

        // 2. Llamar al resto de los Seeders en ORDEN
        $this->call([
            LocationSeeder::class, // Ciudades
            BusSeeder::class,      // Empresas -> Buses -> Asientos
            TripSeeder::class,     // Viajes (une todo lo anterior)
        ]);
    }
}