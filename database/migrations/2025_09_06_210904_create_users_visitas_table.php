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
        Schema::create('users_visitas', function (Blueprint $table) {
            $table->id('visita_id');
            $table->foreignId('visita_user')->constrained('users', 'user_id');
            $table->dateTime('visita_fecha')->nullable();
            $table->integer('visita_estado');
            $table->decimal('visita_venta', 10, 2);
            $table->text('visita_descripcion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_visitas');
    }
};
