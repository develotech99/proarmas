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
        Schema::create('pro_estados_cuenta', function (Blueprint $table) {
            $table->id('ec_id');
            $table->unsignedBigInteger('ec_banco_id')->nullable()->comment('FK al banco de origen (si aplica)');
            $table->string('ec_archivo', 255)->comment('Ruta del archivo subido en storage');
            $table->json('ec_headers')->nullable()->comment('Lista de encabezados detectados en el archivo');
            $table->longText('ec_rows')->nullable()->comment('Contenido de filas normalizado (JSON)');
            $table->date('ec_fecha_ini')->nullable()->comment('Fecha inicio del período (opcional)');
            $table->date('ec_fecha_fin')->nullable()->comment('Fecha fin del período (opcional)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pro_estados_cuenta');
    }
};
