<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pro_pagos_subidos', function (Blueprint $table) {
            $table->id('ps_id');

            // Relaciones
            $table->unsignedInteger('ps_venta_id');            
            $table->unsignedBigInteger('ps_cliente_user_id')->nullable();


            $table->enum('ps_estado', ['PENDIENTE_VALIDACION', 'VALIDADO', 'RECHAZADO'])
                ->default('PENDIENTE_VALIDACION');
            $table->enum('ps_canal', ['WEB', 'CSV', 'MANUAL'])->default('WEB');

            $table->dateTime('ps_fecha_comprobante')->nullable();
            $table->decimal('ps_monto_comprobante', 10, 2);
            $table->decimal('ps_monto_total_cuotas_front', 10, 2)->nullable();
            $table->decimal('ps_diferencia', 10, 2)->nullable(); 

            $table->unsignedBigInteger('ps_banco_id')->nullable();
            $table->string('ps_banco_nombre', 64)->nullable();
            $table->string('ps_referencia', 64)->nullable();
            $table->string('ps_concepto', 255)->nullable();

            $table->json('ps_cuotas_json')->nullable();    
            $table->string('ps_imagen_path', 255)->nullable();

            $table->unsignedBigInteger('ps_validado_por')->nullable(); 
            $table->dateTime('ps_fecha_validacion')->nullable();
            $table->text('ps_observaciones')->nullable();

            $table->string('ps_checksum', 64)->nullable()->unique();

            $table->timestamps();

            // Índices útiles
            $table->index(['ps_venta_id', 'ps_estado']);
            $table->index('ps_referencia');
            $table->index('ps_banco_id');


            $table->foreign('ps_venta_id')
                ->references('ven_id')->on('pro_ventas')
                ->cascadeOnDelete();

            $table->foreign('ps_cliente_user_id')
                ->references('user_id')->on('users')
                ->nullOnDelete();

            $table->foreign('ps_validado_por')
                ->references('user_id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pro_pagos_subidos');
    }
};
