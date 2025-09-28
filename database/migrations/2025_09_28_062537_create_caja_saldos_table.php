<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('caja_saldos', function (Blueprint $table) {
            $table->id('caja_saldo_id');

            $table->unsignedBigInteger('caja_saldo_metodo_pago');

            $table->string('caja_saldo_moneda', 3)->default('GTQ');

            $table->decimal('caja_saldo_monto_actual', 14, 2)->default(0);

            $table->timestamp('caja_saldo_actualizado')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['caja_saldo_metodo_pago', 'caja_saldo_moneda'], 'uk_caja_saldo_metodo_moneda');

            $table->foreign('caja_saldo_metodo_pago')
                ->references('metpago_id')
                ->on('pro_metodos_pago')
                ->cascadeOnUpdate(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caja_saldos');
    }
};
