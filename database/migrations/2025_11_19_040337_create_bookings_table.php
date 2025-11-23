<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\BookingStatus;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            // ULID público para el ticket (ej: URL.com/tickets/01ARZ3...)
            $table->ulid('id')->primary();
            
            $table->foreignId('user_id')->constrained();
            
            // IMPORTANTE: foreignUlid para relacionar con Trips que usa ULID
            $table->foreignUlid('trip_id')->constrained('trips');
            
            $table->foreignId('seat_id')->constrained();
            
            $table->string('status')->default(BookingStatus::PENDING->value);
            $table->string('payment_id')->nullable()->index();
            
            // Guardamos el precio que pagó el usuario (snapshot)
            $table->decimal('price_paid', 10, 2);
            
            $table->timestamps();

            // Índice compuesto para verificar disponibilidad rápidamente
            // Ayuda a responder: "¿Este asiento en este viaje está ocupado?"
            $table->index(['trip_id', 'seat_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};