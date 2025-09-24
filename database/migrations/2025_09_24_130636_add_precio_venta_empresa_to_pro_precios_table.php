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
        Schema::table('pro_precios', function (Blueprint $table) {
            // Agregar el nuevo campo precio_venta_empresa
            $table->decimal('precio_venta_empresa', 10, 2)
                  ->nullable()
                  ->after('precio_venta')
                  ->comment('Precio de venta para empresas/mayoristas');
            
            // Opcional: Agregar índice si planeas hacer consultas frecuentes por este campo
            $table->index('precio_venta_empresa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pro_precios', function (Blueprint $table) {
            // Eliminar índice primero (si lo agregaste)
            $table->dropIndex(['precio_venta_empresa']);
            
            // Luego eliminar la columna
            $table->dropColumn('precio_venta_empresa');
        });
    }
};