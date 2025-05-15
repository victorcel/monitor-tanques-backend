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
            $table->uuid('id')->primary();
            $table->uuid('tank_id');
            $table->float('liquid_level');
            $table->timestamp('reading_timestamp');
            $table->timestamps();
            
            $table->foreign('tank_id')
                  ->references('id')
                  ->on('tanks')
                  ->onDelete('cascade');
            
            // Índice para búsquedas rápidas por tanque
            $table->index('tank_id');
            // Índice para búsquedas por timestamp
            $table->index('reading_timestamp');
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
