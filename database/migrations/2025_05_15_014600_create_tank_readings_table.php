<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tank_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tank_id')->constrained()->onDelete('cascade');
            $table->float('liquid_level')->comment('Nivel de líquido en centímetros');
            $table->float('volume')->comment('Volumen calculado en litros');
            $table->float('percentage')->comment('Porcentaje de llenado');
            $table->float('temperature')->nullable()->comment('Temperatura en grados Celsius');
            $table->timestamp('reading_timestamp');
            $table->json('raw_data')->nullable();
            $table->timestamps();

            // Índice para mejorar rendimiento de consultas
            $table->index(['tank_id', 'reading_timestamp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tank_readings');
    }
};
