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
        Schema::table('users', function (Blueprint $table) {
            // Renombrar columnas existentes
            $table->renameColumn('user_email', 'user');
            $table->renameColumn('user_password', 'password');
        });

        // Aplicar cambios de tipo/restricciones en una segunda operación
        // para evitar conflictos con el renombrado
        Schema::table('users', function (Blueprint $table) {
            $table->string('user', 100)->unique()->change();
            $table->string('password', 255)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Volver al estado anterior
            $table->renameColumn('user', 'user_email');
            $table->renameColumn('password', 'user_password');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('user_email', 100)->unique()->change();
            $table->string('user_password', 255)->change();
        });
    }
};