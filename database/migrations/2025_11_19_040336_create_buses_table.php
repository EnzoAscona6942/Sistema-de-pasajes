<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\ServiceType;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buses', function (Blueprint $table) {
            $table->id();
            // Si borran la empresa, borramos sus buses
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            
            $table->string('plate_number')->unique(); // La patente es única
            $table->string('model')->nullable();
            $table->integer('capacity');
            
            // Usamos el Enum para definir el default
            $table->string('service_type')->default(ServiceType::SEMI_CAMA->value);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buses');
    }
};