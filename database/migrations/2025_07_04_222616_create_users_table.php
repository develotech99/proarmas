<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            // INT UNSIGNED para hacer match con roles.id (increments)
            $table->increments('user_id');

            $table->string('user_primer_nombre', 100)->nullable();
            $table->string('user_segundo_nombre', 100)->nullable();
            $table->string('user_primer_apellido', 100)->nullable();
            $table->string('user_segundo_apellido', 100)->nullable();

            $table->string('user_email', 100)->unique();
            $table->string('user_password');
            $table->string('user_dpi_dni', 20)->nullable();

            $table->unsignedInteger('user_rol')->nullable();

            $table->timestamp('user_fecha_creacion')->useCurrent();
            $table->dateTime('user_fecha_contrasena')->nullable();
            $table->string('user_foto', 250)->nullable();
            $table->string('user_token', 250)->nullable();
            $table->dateTime('user_fecha_verificacion')->nullable();
            $table->boolean('user_situacion')->default(true);

            $table->foreign('user_rol')
                ->references('id')->on('roles')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
