<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ej: "Terminal de Retiro"
            $table->string('city'); // Ej: "CABA"
            $table->string('province'); // Ej: "Buenos Aires"
            $table->timestamps();

            // Índice compuesto para evitar duplicados de nombres en la misma ciudad
            $table->unique(['name', 'city']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};