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
        Schema::create('pro_pagos_subidos', function (Blueprint $table) {
            $table->id('ps_id');
            $table->unsignedInteger('ps_venta_id');
            $table->unsignedInteger('ps_cliente_user_id')->nullable();

            $table->decimal('ps_monto_comprobante', 10, 2);
            $table->decimal('ps_monto_total_cuotas_front', 10, 2)->nullable();
            $table->unsignedBigInteger('ps_banco_id')->nullable();
            $table->string('ps_banco_nombre', 64)->nullable();
            $table->string('ps_referencia', 64)->nullable();
            $table->string('ps_concepto', 255)->nullable();
            $table->json('ps_cuotas_json')->nullable();             
            $table->string('ps_imagen_path', 255)->nullable();     


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pro_pagos_subidos');
    }
};
