<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            // Usamos ULID como Primary Key (Nativo Laravel 10+)
            $table->ulid('id')->primary();
            
            $table->foreignId('origin_id')->constrained('locations');
            $table->foreignId('destination_id')->constrained('locations');
            $table->foreignId('bus_id')->constrained();
            
            $table->dateTime('departure_time');
            $table->dateTime('arrival_time_estimated');
            
            // Precio base con precisión monetaria
            $table->decimal('base_price', 10, 2); 
            
            $table->timestamps();

            // Optimización: Índice para el motor de búsqueda de pasajes
            // "Buscar viajes de A a B en tal fecha"
            $table->index(['origin_id', 'destination_id', 'departure_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};