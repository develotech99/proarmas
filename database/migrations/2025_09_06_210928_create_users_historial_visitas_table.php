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
        Schema::create('users_historial_visitas', function (Blueprint $table) {
            $table->id('hist_id');
            $table->foreignId('hist_visita_id')->constrained('users_visitas', 'visita_id');
            $table->dateTime('hist_fecha_actualizacion');
            $table->integer('hist_estado_anterior')->nullable();
            $table->integer('hist_estado_nuevo');
            $table->decimal('hist_total_venta_anterior', 10, 2)->nullable();
            $table->decimal('hist_total_venta_nuevo', 10, 2)->nullable();
            $table->text('hist_descripcion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_historial_visitas');
    }
};
