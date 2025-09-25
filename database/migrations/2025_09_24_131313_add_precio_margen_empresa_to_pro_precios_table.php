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
            // Agregar margen para empresas después del margen individual
            $table->decimal('precio_margen_empresa', 5, 2)
                  ->nullable()
                  ->after('precio_margen')
                  ->comment('Margen de ganancia para ventas a empresas (%)');
            
            // Índice opcional para consultas
            $table->index('precio_margen_empresa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pro_precios', function (Blueprint $table) {
            // Eliminar índice primero
            $table->dropIndex(['precio_margen_empresa']);
            
            // Luego eliminar la columna
            $table->dropColumn('precio_margen_empresa');
        });
    }
};