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
        Schema::create('users_ubicaciones', function (Blueprint $table) {
            $table->id('ubi_id');
            $table->foreignId('ubi_user')->constrained('users', 'user_id')->onDelete('cascade');
            $table->decimal('ubi_latitud', 9, 6);
            $table->decimal('ubi_longitud', 9, 6);
            $table->string('ubi_descripcion', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_ubicaciones');
    }
};
