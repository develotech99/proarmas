<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agregar tablas de precios y promociones al sistema existente
     */
    public function up(): void
    {
        // Tabla de precios base y especiales
        Schema::create('pro_precios', function (Blueprint $table) {
            $table->integer('precio_id')->autoIncrement()->primary();
            $table->integer('precio_producto_id')->comment('FK al producto');
            $table->decimal('precio_costo', 10, 2)->comment('Precio de compra del producto');
            $table->decimal('precio_venta', 10, 2)->comment('Precio regular de venta');
            $table->decimal('precio_margen', 5, 2)->nullable()->comment('Margen de ganancia estimado (%)');
            $table->decimal('precio_especial', 10, 2)->nullable()->comment('Precio especial, si se aplica');
            $table->string('precio_justificacion', 255)->nullable()->comment('Motivo del precio especial (descuento, promoción, etc)');
            $table->date('precio_fecha_asignacion')->default(DB::raw('CURRENT_DATE'))->comment('Fecha en que se asignó este precio');
            $table->integer('precio_situacion')->default(1)->comment('1 = activo, 0 = histórico o inactivo');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            // Índices
            $table->index('precio_producto_id');
            $table->index(['precio_producto_id', 'precio_situacion']);
            $table->index('precio_fecha_asignacion');
            $table->index('precio_situacion');
            
            // Clave foránea
            $table->foreign('precio_producto_id')
                  ->references('producto_id')
                  ->on('pro_productos')
                  ->onDelete('cascade');
        });

        // Tabla de promociones temporales
        Schema::create('pro_promociones', function (Blueprint $table) {
            $table->integer('promo_id')->autoIncrement()->primary();
            $table->integer('promo_producto_id')->comment('FK al producto promocionado');
            $table->string('promo_nombre', 100)->comment('Nombre de la promoción, ej: Black Friday');
            $table->enum('promo_tipo', ['porcentaje', 'fijo'])->comment('Tipo de descuento aplicado');
            $table->decimal('promo_valor', 10, 2)->comment('Valor del descuento, ej: 25.00 = 25% si es porcentaje');
            $table->decimal('promo_precio_original', 10, 2)->nullable()->comment('Precio antes del descuento (solo para mostrar)');
            $table->decimal('promo_precio_descuento', 10, 2)->nullable()->comment('Precio final con descuento');
            $table->date('promo_fecha_inicio')->comment('Inicio de la promoción');
            $table->date('promo_fecha_fin')->comment('Fin de la promoción');
            $table->string('promo_justificacion', 255)->nullable()->comment('Motivo de la promoción');
            $table->integer('promo_situacion')->default(1)->comment('1 = activa, 0 = expirada o desactivada');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            // Índices
            $table->index('promo_producto_id');
            $table->index(['promo_fecha_inicio', 'promo_fecha_fin']);
            $table->index(['promo_situacion', 'promo_fecha_inicio']);
            $table->index('promo_situacion');
            $table->index('promo_tipo');
            
            // Clave foránea
            $table->foreign('promo_producto_id')
                  ->references('producto_id')
                  ->on('pro_productos')
                  ->onDelete('cascade');
        });

        // Tabla para relacionar empresas de importación con licencias (si no existe)
        if (!Schema::hasTable('pro_empresas_de_importacion')) {
            Schema::create('pro_empresas_de_importacion', function (Blueprint $table) {
                $table->integer('empresaimp_id')->autoIncrement()->primary()->comment('ID empresa importadora');
                $table->integer('empresaimp_pais')->comment('ID del país asociado');
                $table->string('empresaimp_descripcion', 50)->nullable()->comment('tipo: empresa matriz o logística');
                $table->integer('empresaimp_situacion')->default(1)->comment('1 = activa, 0 = inactiva');
                
                // Índices
                $table->index('empresaimp_pais');
                $table->index('empresaimp_situacion');
                
                // Clave foránea (si existe la tabla de países)
                if (Schema::hasTable('pro_paises')) {
                    $table->foreign('empresaimp_pais')
                          ->references('pais_id')
                          ->on('pro_paises')
                          ->onDelete('restrict');
                }
            });
        }

        // Tabla de licencias de importación (si no existe)
        if (!Schema::hasTable('pro_licencias_para_importacion')) {
            Schema::create('pro_licencias_para_importacion', function (Blueprint $table) {
                $table->integer('lipaimp_id')->autoIncrement()->primary();
                $table->integer('lipaimp_poliza')->nullable();
                $table->string('lipaimp_descripcion', 100)->nullable();
                $table->integer('lipaimp_empresa');
                $table->date('lipaimp_fecha_vencimiento')->nullable();
                $table->integer('lipaimp_situacion')->default(1)->comment('1 pendiente, 2 autorizado, 3 rechazado');
                
                // Índices
                $table->index('lipaimp_empresa');
                $table->index('lipaimp_situacion');
                $table->index('lipaimp_fecha_vencimiento');
                
                // Clave foránea
                $table->foreign('lipaimp_empresa')
                      ->references('empresaimp_id')
                      ->on('pro_empresas_de_importacion')
                      ->onDelete('restrict');
            });
        }
    }

    /**
     * Reversar las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('pro_licencias_para_importacion');
        Schema::dropIfExists('pro_empresas_de_importacion');
        Schema::dropIfExists('pro_promociones');
        Schema::dropIfExists('pro_precios');
    }
};