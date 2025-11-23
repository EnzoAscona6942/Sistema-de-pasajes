<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seats', function (Blueprint $table) {
            $table->id();
            // Si borran el bus, borramos sus asientos
            $table->foreignId('bus_id')->constrained()->cascadeOnDelete();
            
            $table->string('seat_number'); // String (Ej: "01", "4A")
            $table->integer('floor')->default(1); // Piso 1 o 2
            $table->string('type')->nullable(); // Para sobreescribir el tipo del bus si es mixto
            $table->timestamps();

            // Regla de integridad: No puede haber dos asientos número "4" en el mismo bus
            $table->unique(['bus_id', 'seat_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seats');
    }
};