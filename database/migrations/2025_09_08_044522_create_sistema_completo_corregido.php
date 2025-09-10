<?php

// MIGRACIÓN COMPLETA DEL SISTEMA CORREGIDA
// Archivo: database/migrations/2024_01_01_000001_create_sistema_armas_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // ========================
        // ENTIDADES FUERTES
        // ========================

        // Métodos de pago
        Schema::create('pro_metodos_pago', function (Blueprint $table) {
            $table->id('metpago_id')->comment('ID método de pago');
            $table->string('metpago_descripcion', 50)->comment('efectivo, transferencia, etc.');
            $table->integer('metpago_situacion')->default(1)->comment('1 = activo, 0 = inactivo');
            $table->timestamps();
        });

        // Países
        Schema::create('pro_paises', function (Blueprint $table) {
            $table->id('pais_id')->comment('ID de país');
            $table->string('pais_descripcion', 50)->nullable()->comment('Descripción del país');
            $table->integer('pais_situacion')->default(1)->comment('1 = activo, 0 = inactivo');
            $table->timestamps();
        });

        
      // Tabla de marcas
      Schema::create('pro_marcas', function (Blueprint $table) {
        $table->id('marca_id')->autoIncrement()->primary()->comment('ID de marca');
        $table->string('marca_descripcion', 50)->nullable()->comment('system defense, glock, brigade');
        $table->integer('marca_situacion')->default(1)->comment('1 = activa, 0 = inactiva');
        
        // Índices
        $table->index('marca_situacion');
    });

    // Tabla de modelos
    Schema::create('pro_modelo', function (Blueprint $table) {
        $table->id('modelo_id')->autoIncrement()->primary()->comment('ID de modelo');
        $table->string('modelo_descripcion', 50)->nullable()->comment('c9, bm-f-9, sd15');
        $table->integer('modelo_situacion')->default(1)->comment('1 = activo, 0 = inactivo');
        $table->unsignedBigInteger('modelo_marca_id')->nullable();
        
        // Índices
        $table->index('modelo_marca_id');
        $table->index('modelo_situacion');
        
        // Clave foránea
        $table->foreign('modelo_marca_id')
              ->references('marca_id')
              ->on('pro_marcas')
              ->onDelete('set null');
    });
 

        // Unidades de medida
        Schema::create('pro_unidades_medida', function (Blueprint $table) {
            $table->id('unidad_id');
            $table->string('unidad_nombre', 50);
            $table->string('unidad_abreviacion', 10);
            $table->string('unidad_tipo', 20)->default('longitud');
            $table->integer('unidad_situacion')->default(1);
            $table->timestamps();
        });

        // Calibres
        Schema::create('pro_calibres', function (Blueprint $table) {
            $table->id('calibre_id');
            $table->string('calibre_nombre', 20);
            $table->unsignedBigInteger('calibre_unidad_id');
            $table->decimal('calibre_equivalente_mm', 6, 2)->nullable();
            $table->integer('calibre_situacion')->default(1);
            $table->timestamps();

            $table->foreign('calibre_unidad_id')->references('unidad_id')->on('pro_unidades_medida');
        });

        // Categorías
        Schema::create('pro_categorias', function (Blueprint $table) {
            $table->id('categoria_id');
            $table->string('categoria_nombre', 100);
            $table->integer('categoria_situacion')->default(1);
            $table->timestamps();
        });

        // Subcategorías
        Schema::create('pro_subcategorias', function (Blueprint $table) {
            $table->id('subcategoria_id');
            $table->string('subcategoria_nombre', 100);
            $table->unsignedBigInteger('subcategoria_idcategoria');
            $table->integer('subcategoria_situacion')->default(1);
            $table->timestamps();

            $table->foreign('subcategoria_idcategoria')
                  ->references('categoria_id')
                  ->on('pro_categorias')
                  ->onDelete('cascade');
        });

        // ========================
        // EMPRESAS E IMPORTACIONES
        // ========================

        // Empresas de importación
        Schema::create('pro_empresas_de_importacion', function (Blueprint $table) {
            $table->id('empresaimp_id')->comment('ID empresa importadora');
            $table->unsignedBigInteger('empresaimp_pais')->comment('ID del país asociado');
            $table->string('empresaimp_descripcion', 50)->nullable()->comment('tipo: empresa matriz o logística');
            $table->integer('empresaimp_situacion')->default(1)->comment('1 = activa, 0 = inactiva');
            $table->timestamps();
            
            $table->foreign('empresaimp_pais')->references('pais_id')->on('pro_paises');
        });

        // Licencias para importación
          // Tabla de licencias de importación (si no existe)
          if (!Schema::hasTable('pro_licencias_para_importacion')) {
          Schema::create('pro_licencias_para_importacion', function (Blueprint $table) {
            $table->id('lipaimp_id');
            $table->unsignedBigInteger('lipaimp_modelo')->comment('modelo importado');
            $table->decimal('lipaimp_largo_canon', 10, 2)->comment('Largo del cañón');
            $table->integer('lipaimp_poliza')->nullable()->comment('Número de póliza de la licencia');
            $table->string('lipaimp_numero_licencia', 50)->nullable()->comment('Número oficial de la licencia');
            $table->string('lipaimp_descripcion', 255)->nullable()->comment('Descripción general del lote de armas');
            $table->unsignedBigInteger('lipaimp_empresa')->comment('Empresa importadora');
            $table->date('lipaimp_fecha_emision')->nullable()->comment('Fecha de emisión de la licencia');
            $table->date('lipaimp_fecha_vencimiento')->nullable()->comment('Fecha de vencimiento');
            $table->text('lipaimp_observaciones')->nullable()->comment('Observaciones adicionales');
            $table->integer('lipaimp_situacion')->default(1)->comment('1 pendiente, 2 autorizado, 3 rechazado, 4 en tránsito, 5 recibido');
            $table->integer('lipaimp_cantidad_armas')->default(0)->comment('Total de armas en esta licencia');
            $table->timestamps();

            // Índices
            $table->index('lipaimp_empresa', 'idx_lipaimp_empresa');
            $table->index('lipaimp_situacion', 'idx_lipaimp_situacion');
            $table->index('lipaimp_fecha_vencimiento', 'idx_lipaimp_fecha_vencimiento');
            $table->index('lipaimp_numero_licencia', 'idx_lipaimp_numero_licencia');
            $table->index('lipaimp_poliza', 'idx_lipaimp_poliza');
            $table->unique('lipaimp_numero_licencia', 'uniq_lipaimp_numero');

            // Claves foráneas
            $table->foreign('lipaimp_empresa', 'fk_lipaimp_empresa')
                  ->references('empresaimp_id')
                  ->on('pro_empresas_de_importacion')
                  ->onDelete('restrict');

            $table->foreign('lipaimp_modelo', 'fk_lipaimp_modelo')
                  ->references('modelo_id')
                  ->on('pro_modelo')
                  ->onDelete('restrict');

        });
        }
    

        // Digecam
        Schema::create('pro_digecam', function (Blueprint $table) {
            $table->id('digecam_id')->comment('ID digecam');
            $table->unsignedBigInteger('digecam_licencia_import')->comment('Licencia asociada');
            $table->string('digecam_autorizacion', 50)->default('no aprobada')->comment('Estado autorización');
            $table->integer('digecam_situacion')->default(1)->comment('1 = activa, 0 = inactiva');
            $table->timestamps();
            
           
        });

        // ========================
        // INVENTARIO 
        // ========================

        // Inventario modelos (CORREGIDO: cambio de modelo_id a inv_modelo_id)
        Schema::create('pro_inventario_modelos', function (Blueprint $table) {
            $table->id('inv_modelo_id')->comment('ID del inventario de modelo/lote');
            $table->unsignedBigInteger('modelo_licencia')->comment('Licencia de importación asociada');
            $table->integer('modelo_poliza')->comment('No. de póliza/factura de compra');
            $table->date('modelo_fecha_ingreso')->comment('Fecha de ingreso del lote');
            $table->unsignedBigInteger('modelo_marca');
            $table->unsignedBigInteger('modelo_modelo');
            $table->unsignedBigInteger('modelo_calibre')->nullable();
            $table->integer('modelo_cantidad')->default(0)->comment('Cantidad total en este lote');
            $table->integer('modelo_disponible')->default(0)->comment('Stock disponible');
            $table->integer('modelo_situacion')->default(1)->comment('1 = activo, 0 = inactivo');
            $table->timestamps();
            
            $table->foreign('modelo_licencia')->references('lipaimp_id')->on('pro_licencias_para_importacion');
            $table->foreign('modelo_marca')->references('marca_id')->on('pro_marcas');
            $table->foreign('modelo_modelo')->references('modelo_id')->on('pro_modelo');
            $table->foreign('modelo_calibre')->references('calibre_id')->on('pro_calibres');
        });

        // Inventario armas (CORREGIDO: referencia cambiada a inv_modelo_id)
        Schema::create('pro_inventario_armas', function (Blueprint $table) {
            $table->id('arma_id')->comment('ID correlativo');
            $table->unsignedBigInteger('arma_modelo_id')->comment('Referencia al lote o modelo');
            $table->string('arma_numero_serie', 200)->unique()->nullable()->comment('Número de serie de la pistola');
            $table->enum('arma_estado', ['disponible','vendida','reservada','baja'])->default('disponible');
            $table->timestamps();
            
            $table->foreign('arma_modelo_id')->references('inv_modelo_id')->on('pro_inventario_modelos');
        });

        // Armas licenciadas
        Schema::create('pro_armas_licenciadas', function (Blueprint $table) {
            $table->id('arma_lic_id')->comment('ID arma licenciada');
            $table->unsignedBigInteger('arma_licencia_id')->comment('Licencia a la que pertenece');
            $table->unsignedBigInteger('arma_subcate_id')->comment('Subcategoría del arma');
            $table->unsignedBigInteger('arma_calibre_id')->comment('Calibre del arma');
            $table->string('arma_serial', 100)->nullable()->comment('Número de serie del arma (único por arma)');
            $table->string('arma_numero_inventario', 50)->nullable()->comment('Número de inventario interno');
            $table->integer('arma_cantidad')->default(1)->comment('Cantidad de este tipo de arma');
            $table->decimal('arma_precio_unitario', 12, 2)->nullable()->comment('Precio unitario del arma');
            $table->decimal('arma_valor_total', 12, 2)->nullable()->comment('Valor total (cantidad * precio)');
            $table->string('arma_estado_fisico', 50)->nullable()->comment('Nuevo, Usado, Reparado, etc.');
            $table->integer('arma_situacion')->default(1)->comment('1 pendiente, 2 en tránsito, 3 recibido, 4 verificado, 5 en bodega, 6 vendido');
            $table->text('arma_observaciones')->nullable()->comment('Observaciones específicas del arma');
            $table->date('arma_fecha_verificacion')->nullable()->comment('Fecha de verificación física');
            $table->string('arma_responsable_verificacion', 100)->nullable()->comment('Persona que verificó el arma');
            $table->timestamps();

            // Índices
            $table->index('arma_licencia_id', 'idx_arma_licencia_id');
            $table->index('arma_situacion', 'idx_arma_situacion');
            $table->index('arma_serial', 'idx_arma_serial');
            $table->index('arma_numero_inventario', 'idx_arma_numero_inventario');
            $table->index('arma_calibre_id', 'idx_arma_calibre_id');
            $table->index('arma_subcate_id', 'idx_arma_subcate_id');
            $table->unique('arma_serial', 'uniq_arma_serial');
            $table->unique('arma_numero_inventario', 'uniq_arma_inventario');

            // Claves foráneas
            $table->foreign('arma_licencia_id', 'fk_arma_licencia')
                  ->references('lipaimp_id')
                  ->on('pro_licencias_para_importacion')
                  ->onDelete('cascade');

            $table->foreign('arma_subcate_id', 'fk_arma_subcate')
                  ->references('subcategoria_id')
                  ->on('pro_subcategorias')
                  ->onDelete('restrict');

            $table->foreign('arma_calibre_id', 'fk_arma_calibre')
                  ->references('calibre_id')
                  ->on('pro_calibres')
                  ->onDelete('restrict');
        });

        // ========================
        // CLIENTES Y VENTAS
        // ========================

        // Clientes
        Schema::create('pro_clientes', function (Blueprint $table) {
            $table->id('cliente_id');
            $table->enum('tipo', ['empresa','persona']);
            $table->string('nombre_empresa', 200)->nullable();
            $table->string('nombre', 200)->comment('NOMBRE DEL DUENO DE LA EMPRESA O PERSONA INDIVIDUAL');
            $table->string('razon_social', 200)->nullable()->comment('solo para empresas');
            $table->string('ubicacion', 100)->nullable();
            $table->integer('situacion')->default(1);
            $table->timestamps();
        });

        // Ventas
        Schema::create('pro_ventas', function (Blueprint $table) {
            $table->id('venta_id');
            $table->unsignedBigInteger('cliente_id');
            $table->string('factura', 200)->nullable();
            $table->date('fecha');
            $table->integer('autorizacion');
            $table->integer('situacion')->default(1);
            $table->string('observaciones', 200)->nullable();
            $table->timestamps();
            
            $table->foreign('cliente_id')->references('cliente_id')->on('pro_clientes');
        });

        // Detalle venta (CORREGIDO: referencia cambiada a inv_modelo_id)
        Schema::create('pro_detalle_venta', function (Blueprint $table) {
            $table->id('detalle_id');
            $table->unsignedBigInteger('venta_id');
            $table->unsignedBigInteger('modelo_id')->nullable()->comment('Si la venta es por lote/cantidad');
            $table->unsignedBigInteger('arma_id')->nullable()->comment('Si la venta es por arma única');
            $table->integer('cantidad')->default(1);
            $table->decimal('precio_unitario', 12, 2);
            $table->timestamps();
            
            $table->foreign('venta_id')->references('venta_id')->on('pro_ventas');
            $table->foreign('modelo_id')->references('inv_modelo_id')->on('pro_inventario_modelos');
            $table->foreign('arma_id')->references('arma_id')->on('pro_inventario_armas');
        });

        // ========================
        // PAGOS DE VENTAS
        // ========================

        // Pagos
        Schema::create('pro_pagos', function (Blueprint $table) {
            $table->id('pago_id');
            $table->unsignedBigInteger('venta_id');
            $table->enum('venta_tipo', ['empresa','persona']);
            $table->date('pago_fecha');
            $table->decimal('pago_monto', 12, 2);
            $table->unsignedBigInteger('pago_metodo');
            $table->integer('pago_num_cuota')->default(1);
            $table->timestamps();
            
            $table->foreign('pago_metodo')->references('metpago_id')->on('pro_metodos_pago');
        });

        // Comprobantes pago ventas
        Schema::create('pro_comprobantes_pago_ventas', function (Blueprint $table) {
            $table->id('comprobventas_id');
            $table->string('comprobventas_ruta', 255);
            $table->unsignedBigInteger('comprobventas_pago_id');
            $table->tinyInteger('comprobventas_situacion')->default(1);
            $table->timestamps();
            
            $table->foreign('comprobventas_pago_id')->references('pago_id')->on('pro_pagos');
        });

        // ========================
        // PAGOS DE LICENCIAS
        // ========================

        // Pagos licencias
        Schema::create('pro_pagos_licencias', function (Blueprint $table) {
            $table->id('pago_lic_id')->comment('ID pago licencia');
            $table->unsignedBigInteger('pago_licencia_id');
            $table->unsignedBigInteger('pago_empresa_id');
            $table->date('pago_fecha');
            $table->decimal('pago_monto', 10, 2);
            $table->unsignedBigInteger('pago_metodo');
            $table->string('pago_verificado', 50)->default('no aprobada');
            $table->string('pago_concepto', 250)->nullable();
            $table->timestamps();
            
            $table->foreign('pago_licencia_id')->references('lipaimp_id')->on('pro_licencias_para_importacion');
            $table->foreign('pago_empresa_id')->references('empresaimp_id')->on('pro_empresas_de_importacion');
            $table->foreign('pago_metodo')->references('metpago_id')->on('pro_metodos_pago');
        });

        // Comprobantes pago
        Schema::create('pro_comprobantes_pago', function (Blueprint $table) {
            $table->id('comprob_id');
            $table->string('comprob_ruta', 50)->nullable();
            $table->unsignedBigInteger('comprob_pagos_licencia')->nullable();
            $table->integer('comprob_situacion')->default(1);
            $table->timestamps();
            
            $table->foreign('comprob_pagos_licencia')->references('pago_lic_id')->on('pro_pagos_licencias');
        });

        // Documentación licencia import
        Schema::create('pro_documentacion_lic_import', function (Blueprint $table) {
            $table->id('doclicimport_id');
            $table->string('doclicimport_ruta', 50);
            $table->unsignedBigInteger('doclicimport_num_lic')->nullable();
            $table->integer('doclicimport_situacion')->default(1);
            $table->timestamps();
            
            $table->foreign('doclicimport_num_lic')->references('lipaimp_id')->on('pro_licencias_para_importacion');
        });

        // ========================
        // NUEVAS TABLAS DE PRODUCTOS
        // =======================
              // =========================================
        // TABLAS DE INVENTARIO
        // =========================================

        // Tabla de productos
        Schema::create('pro_productos', function (Blueprint $table) {
            $table->id('producto_id')->autoIncrement()->primary();
            $table->string('producto_nombre', 100);
            $table->string('producto_codigo_barra', 100)->unique()->nullable()->comment('si aplica');
            $table->unsignedBigInteger('producto_categoria_id');
            $table->unsignedBigInteger('producto_subcategoria_id');
            $table->unsignedBigInteger('producto_marca_id');
            $table->unsignedBigInteger('producto_modelo_id')->nullable()->comment('NULL si no aplica');
            $table->unsignedBigInteger('producto_calibre_id')->nullable()->comment('NULL si no aplica');
            $table->boolean('producto_requiere_serie')->default(false);
            $table->boolean('producto_es_importado')->default(false)->comment('true = importación, false = compra local');
            $table->unsignedBigInteger('producto_id_licencia')->nullable()->comment('Si viene de importación, se guarda aquí el ID de la licencia');
            $table->integer('producto_situacion')->default(1)->comment('1 = activo, 0 = inactivo');
            
            // Índices para optimizar consultas
            $table->index('producto_categoria_id');
            $table->index('producto_subcategoria_id');
            $table->index('producto_marca_id');
            $table->index('producto_modelo_id');
            $table->index('producto_calibre_id');
            $table->index('producto_situacion');
            $table->index(['producto_situacion', 'producto_categoria_id']);
            $table->index('producto_requiere_serie');
            
            // Claves foráneas
            $table->foreign('producto_categoria_id')
                  ->references('categoria_id')
                  ->on('pro_categorias')
                  ->onDelete('restrict');
                  
            $table->foreign('producto_subcategoria_id')
                  ->references('subcategoria_id')
                  ->on('pro_subcategorias')
                  ->onDelete('restrict');
                  
            $table->foreign('producto_marca_id')
                  ->references('marca_id')
                  ->on('pro_marcas')
                  ->onDelete('restrict');
                  
            $table->foreign('producto_modelo_id')
                  ->references('modelo_id')
                  ->on('pro_modelo')
                  ->onDelete('set null');
                  
            $table->foreign('producto_calibre_id')
                  ->references('calibre_id')
                  ->on('pro_calibres')
                  ->onDelete('set null');
        });

        // Tabla de fotos de productos (CORREGIDA - foreign key apunta a foto_producto_id)
        Schema::create('pro_productos_fotos', function (Blueprint $table) {
            $table->id('foto_id')->autoIncrement()->primary();
            $table->unsignedBigInteger('foto_producto_id');
            $table->string('foto_url', 255);
            $table->boolean('foto_principal')->default(false);
            $table->integer('foto_situacion')->default(1);
            
            // Índices
            $table->index('foto_producto_id');
            $table->index(['foto_producto_id', 'foto_situacion']);
            $table->index('foto_principal');
            
            // Clave foránea CORREGIDA
            $table->foreign('foto_producto_id')
                  ->references('producto_id')
                  ->on('pro_productos')
                  ->onDelete('cascade');
        });

        // Tabla de series de productos
        Schema::create('pro_series_productos', function (Blueprint $table) {
            $table->id('serie_id')->autoIncrement()->primary();
            $table->unsignedBigInteger('serie_producto_id');
            $table->string('serie_numero_serie', 200)->unique();
            $table->enum('serie_estado', ['disponible', 'reservado', 'vendido', 'baja'])->default('disponible');
            $table->timestamp('serie_fecha_ingreso')->useCurrent();
            $table->integer('serie_situacion')->default(1);
            
            // Índices para optimizar búsquedas
            $table->index('serie_producto_id');
            $table->index('serie_estado');
            $table->index(['serie_producto_id', 'serie_estado']);
            $table->index(['serie_estado', 'serie_situacion']);
            $table->index('serie_numero_serie');
            $table->index('serie_fecha_ingreso');
            
            // Clave foránea
            $table->foreign('serie_producto_id')
                  ->references('producto_id')
                  ->on('pro_productos')
                  ->onDelete('cascade');
        });

        // Tabla de lotes
        Schema::create('pro_lotes', function (Blueprint $table) {
            $table->id('lote_id')->autoIncrement()->primary();
            $table->string('lote_codigo', 100)->unique()->comment("Ej: 'L2025-08-GLOCK-001'");
            $table->timestamp('lote_fecha')->useCurrent();
            $table->string('lote_descripcion', 255)->nullable();
            $table->integer('lote_situacion')->default(1);
            
            // Índices
            $table->index('lote_situacion');
            $table->index('lote_fecha');
            $table->index('lote_codigo');
        });


        // Tabla de movimientos de inventario (CORREGIDA - mov_tipo con tipo de dato)
        Schema::create('pro_movimientos', function (Blueprint $table) {
            $table->id('mov_id')->autoIncrement()->primary();
            $table->unsignedBigInteger('mov_producto_id');
            $table->string('mov_tipo', 50)->comment('ingreso, egreso, baja, importación, ajuste');
            $table->string('mov_origen', 100)->nullable()->comment('importación, ajuste, venta, compra local, etc.');
            $table->integer('mov_cantidad');
            $table->timestamp('mov_fecha')->useCurrent();
            $table->unsignedBigInteger('mov_usuario_id');
            $table->unsignedBigInteger('mov_lote_id')->nullable()->comment('NULL si no aplica');
            $table->string('mov_observaciones', 250)->nullable();
            $table->integer('mov_situacion')->default(1);
            
            // Índices para reportes y consultas frecuentes
            $table->index('mov_producto_id');
            $table->index('mov_tipo');
            $table->index('mov_fecha');
            $table->index('mov_usuario_id');
            $table->index('mov_lote_id');
            $table->index('mov_situacion');
            $table->index(['mov_producto_id', 'mov_tipo']);
            $table->index(['mov_fecha', 'mov_tipo']);
            $table->index(['mov_producto_id', 'mov_fecha']);
            $table->index(['mov_situacion', 'mov_tipo']);
            $table->index(['mov_fecha', 'mov_situacion']);
            
            // Claves foráneas
            $table->foreign('mov_producto_id')
                  ->references('producto_id')
                  ->on('pro_productos')
                  ->onDelete('cascade');
                  
            $table->foreign('mov_lote_id')
                  ->references('lote_id')
                  ->on('pro_lotes')
                  ->onDelete('set null');
        });


        // Tabla de precios base y especiales
        Schema::create('pro_precios', function (Blueprint $table) {
            $table->id('precio_id')->autoIncrement()->primary();
            $table->unsignedBigInteger('precio_producto_id')->comment('FK al producto');
            $table->decimal('precio_costo', 10, 2)->comment('Precio de compra del producto');
            $table->decimal('precio_venta', 10, 2)->comment('Precio regular de venta');
            $table->decimal('precio_margen', 5, 2)->nullable()->comment('Margen de ganancia estimado (%)');
            $table->decimal('precio_especial', 10, 2)->nullable()->comment('Precio especial, si se aplica');
            $table->string('precio_justificacion', 255)->nullable()->comment('Motivo del precio especial (descuento, promoción, etc)');
            $table->date('precio_fecha_asignacion')->comment('Fecha en que se asignó este precio');
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
            $table->id('promo_id')->autoIncrement()->primary();
            $table->unsignedBigInteger('promo_producto_id')->comment('FK al producto promocionado');
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
                $table->id('empresaimp_id')->autoIncrement()->primary()->comment('ID empresa importadora');
                $table->unsignedBigInteger('empresaimp_pais')->comment('ID del país asociado');
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

    }

    public function down()
    {
        // Eliminar en orden inverso (dependencias primero)
        Schema::dropIfExists('pro_productos_fotos');
        Schema::dropIfExists('pro_movimientos');
        Schema::dropIfExists('pro_series_productos');
        Schema::dropIfExists('pro_productos');
        Schema::dropIfExists('pro_lotes');
        Schema::dropIfExists('pro_comprobantes_pago');
        Schema::dropIfExists('pro_pagos_licencias');
        Schema::dropIfExists('pro_documentacion_lic_import');
        Schema::dropIfExists('pro_comprobantes_pago_ventas');
        Schema::dropIfExists('pro_pagos');
        Schema::dropIfExists('pro_detalle_venta');
        Schema::dropIfExists('pro_ventas');
        Schema::dropIfExists('pro_clientes');
        Schema::dropIfExists('pro_armas_licenciadas');
        Schema::dropIfExists('pro_inventario_armas');
        Schema::dropIfExists('pro_inventario_modelos');
        Schema::dropIfExists('pro_digecam');
        Schema::dropIfExists('pro_licencias_para_importacion');
        Schema::dropIfExists('pro_empresas_de_importacion');
        Schema::dropIfExists('pro_subcategorias');
        Schema::dropIfExists('pro_categorias');
        Schema::dropIfExists('pro_calibres');
        Schema::dropIfExists('pro_unidades_medida');
        Schema::dropIfExists('pro_modelo');
        Schema::dropIfExists('pro_marcas');
        Schema::dropIfExists('pro_paises');
        Schema::dropIfExists('pro_metodos_pago');
        Schema::dropIfExists('pro_promociones');
        Schema::dropIfExists('pro_precios');
    }
};