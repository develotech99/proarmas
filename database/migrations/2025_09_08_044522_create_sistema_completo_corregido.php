<?php

// MIGRACIÓN COMPLETA DEL SISTEMA CORREGIDA
// Archivo: database/migrations/2024_01_01_000001_create_sistema_armas_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
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
                $table->engine = 'InnoDB';

                $table->unsignedBigInteger('lipaimp_id')->comment('Número de licencia');
                $table->integer('lipaimp_poliza')->nullable()->comment('Número de póliza de la licencia');
                $table->string('lipaimp_descripcion', 255)->nullable()->comment('Descripción general del lote de armas');
                $table->date('lipaimp_fecha_emision')->nullable()->comment('Fecha de emisión de la licencia');
                $table->date('lipaimp_fecha_vencimiento')->nullable()->comment('Fecha de vencimiento');
                $table->text('lipaimp_observaciones')->nullable()->comment('Observaciones adicionales');
                $table->integer('lipaimp_situacion')->default(1)->comment('1 pendiente, 2 autorizado, 3 rechazado, 4 en tránsito, 5 recibido');
                $table->timestamps();

                $table->primary('lipaimp_id');
            });
        }


        Schema::create('pro_armas_licenciadas', function (Blueprint $table) {
            // Opcionales (MySQL)
            $table->engine = 'InnoDB';
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->bigIncrements('arma_lic_id')->comment('ID arma licenciada');

            $table->unsignedBigInteger('arma_num_licencia'); // FK → pro_licencias_para_importacion.lipaimp_id
            $table->unsignedBigInteger('arma_sub_cat')->comment('subcategoria'); // FK → pro_subcategorias.subcategoria_id
            $table->unsignedBigInteger('arma_modelo')->comment('modelo'); // FK → pro_modelo.modelo_id
            $table->unsignedBigInteger('arma_empresa')->comment('empresa'); // FK → pro_empresas_de_importacion.empresaimp_id
            $table->unsignedBigInteger('arma_calibre')->comment('Calibre del arma');
            $table->decimal('arma_largo_canon', 10, 2)->comment('largo del canon');
            $table->integer('arma_cantidad')->default(1)->comment('Cantidad de este tipo de arma');

            // Índices
            $table->index('arma_num_licencia', 'idx_arma_num_licencia');
            $table->index('arma_sub_cat', 'idx_arma_sub_cat');
            $table->index('arma_modelo', 'idx_arma_modelo');
            $table->index('arma_empresa', 'idx_arma_empresa');

            // Llaves foráneas (nombres iguales a tu DDL)
            $table->foreign('arma_empresa', 'fk_arlic_empresa')
                ->references('empresaimp_id')->on('pro_empresas_de_importacion')
                ->onDelete('restrict');

            $table->foreign('arma_num_licencia', 'fk_arlic_lic')
                ->references('lipaimp_id')->on('pro_licencias_para_importacion')
                ->onDelete('cascade');

            $table->foreign('arma_modelo', 'fk_arlic_modelo')
                ->references('modelo_id')->on('pro_modelo')
                ->onDelete('restrict');

            $table->foreign('arma_sub_cat', 'fk_arlic_sub')
                ->references('subcategoria_id')->on('pro_subcategorias')
                ->onDelete('restrict');
            $table->foreign('arma_calibre', 'fkarma_calibre')
                ->references('calibre_id')->on('pro_calibres')
                ->onDelete('restrict');
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
            $table->enum('arma_estado', ['disponible', 'vendida', 'reservada', 'baja'])->default('disponible');
            $table->timestamps();

            $table->foreign('arma_modelo_id')->references('inv_modelo_id')->on('pro_inventario_modelos');
        });

        // Armas licenciadas


        // ========================
        // CLIENTES Y VENTAS
        // ========================

        // Clientes
        Schema::create('pro_clientes', function (Blueprint $table) {
            $table->id('cliente_id');
            $table->enum('tipo', ['empresa', 'persona']);
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
            $table->enum('venta_tipo', ['empresa', 'persona']);
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

Schema::create('pro_licencias_total_pagado', function (Blueprint $table) {
            $table->unsignedBigInteger('lic_id')->primary();
            $table->decimal('total_pagado', 12, 2)->default(0);
            $table->timestamp('updated_at')->nullable();
            
            $table->foreign('lic_id')
                  ->references('lipaimp_id')
                  ->on('pro_licencias_para_importacion')
                  ->onDelete('cascade');
        });

        // Tabla principal de pagos de licencias
        Schema::create('pro_pagos_licencias', function (Blueprint $table) {
            $table->id('pago_lic_id');
            $table->unsignedBigInteger('pago_lic_licencia_id');
            $table->decimal('pago_lic_total', 12, 2)->default(0);
            $table->tinyInteger('pago_lic_situacion')->default(1);
            $table->timestamps();

            // Foreign keys
            $table->foreign('pago_lic_licencia_id')
                  ->references('lipaimp_id')
                  ->on('pro_licencias_para_importacion')
                  ->onDelete('cascade');

            // Índices
            $table->index('pago_lic_licencia_id');
            $table->index('pago_lic_situacion');
        });

        // Tabla de métodos de pago
        Schema::create('pro_pagos_lic_metodos', function (Blueprint $table) {
            $table->id('pagomet_id');
            $table->unsignedBigInteger('pagomet_pago_lic');
            $table->unsignedBigInteger('pagomet_metodo');
            $table->decimal('pagomet_monto', 12, 2)->default(0);
            $table->char('pagomet_moneda', 3)->default('GTQ');
            $table->string('pagomet_referencia', 100)->nullable();
            $table->string('pagomet_banco', 100)->nullable();
            $table->string('pagomet_nota', 255)->nullable();
            $table->tinyInteger('pagomet_situacion')->default(1);
            $table->timestamps();

            // Foreign keys
            $table->foreign('pagomet_pago_lic')
                  ->references('pago_lic_id')
                  ->on('pro_pagos_licencias')
                  ->onDelete('cascade');

            $table->foreign('pagomet_metodo')
                  ->references('metpago_id')
                  ->on('pro_metodos_pago');

            // Índices
            $table->index('pagomet_pago_lic');
            $table->index('pagomet_metodo');
            $table->index(['pagomet_situacion', 'pagomet_pago_lic']);
        });

        // Tabla de comprobantes de pago
        Schema::create('pro_comprobantes_pago_licencias', function (Blueprint $table) {
            $table->id('comprob_id');
            $table->string('comprob_ruta', 255);
            $table->string('comprob_nombre_original', 255)->nullable();
            $table->unsignedBigInteger('comprob_size_bytes')->default(0);
            $table->string('comprob_mime', 100)->nullable();
            $table->unsignedBigInteger('comprob_pagomet_id');
            $table->tinyInteger('comprob_situacion')->default(1);
            $table->timestamps();

            // Foreign keys
            $table->foreign('comprob_pagomet_id')
                  ->references('pagomet_id')
                  ->on('pro_pagos_lic_metodos')
                  ->onDelete('cascade');

            // Índices
            $table->index('comprob_pagomet_id');
            $table->index('comprob_situacion');
        });


        // Documentación licencia import
        Schema::create('pro_documentacion_lic_import', function (Blueprint $table) {
            $table->bigIncrements('doclicimport_id');
            $table->string('doclicimport_ruta', 255); // ↑ más largo
            $table->string('doclicimport_nombre_original', 255)->nullable();
            $table->unsignedBigInteger('doclicimport_size_bytes')->default(0);
            $table->string('doclicimport_mime', 100)->nullable();

            $table->unsignedBigInteger('doclicimport_num_lic'); // mejor required
            $table->tinyInteger('doclicimport_situacion')->default(1);
            $table->timestamps();

            $table->foreign('doclicimport_num_lic')
                ->references('lipaimp_id')->on('pro_licencias_para_importacion')
                ->cascadeOnDelete(); // 👈 cascada

            $table->index('doclicimport_num_lic'); // 👈 índice
        });

        // ========================
        // NUEVAS TABLAS DE PRODUCTOS
        // =======================
        // =========================================
        // TABLAS DE INVENTARIO
        // =========================================================

        Schema::create('pro_productos', function (Blueprint $table) {
            $table->id('producto_id');
            $table->string('producto_nombre', 100);
            $table->text('producto_descripcion')->nullable()->comment('Descripción detallada del producto');
            $table->string('pro_codigo_sku', 100)->unique()->comment('SKU único autogenerado');
            $table->string('producto_codigo_barra', 100)->unique()->nullable()->comment('Código de barra si aplica');
            $table->unsignedBigInteger('producto_categoria_id');
            $table->unsignedBigInteger('producto_subcategoria_id');
            $table->unsignedBigInteger('producto_marca_id');
            $table->unsignedBigInteger('producto_modelo_id')->nullable()->comment('NULL si no aplica');
            $table->unsignedBigInteger('producto_calibre_id')->nullable()->comment('NULL si no aplica');
            $table->unsignedBigInteger('producto_madein')->nullable()->comment('País de fabricación');
            $table->boolean('producto_requiere_serie')->default(false);
            // REMOVIDO: producto_es_importado
            // REMOVIDO: producto_id_licencia
            $table->integer('producto_stock_minimo')->default(0)->comment('Alerta de stock mínimo');
            $table->integer('producto_stock_maximo')->default(0)->comment('Stock máximo recomendado');
            $table->integer('producto_situacion')->default(1)->comment('1 = activo, 0 = inactivo');
            $table->timestamps();

            // Índices para optimizar consultas
            $table->index('producto_categoria_id');
            $table->index('producto_subcategoria_id');
            $table->index('producto_marca_id');
            $table->index('producto_modelo_id');
            $table->index('producto_calibre_id');
            $table->index('producto_situacion');
            $table->index('producto_codigo_barra');
            $table->index('pro_codigo_sku');
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

            // FK a países si la tabla existe
            if (Schema::hasTable('pro_paises')) {
                $table->foreign('producto_madein')
                    ->references('pais_id')
                    ->on('pro_paises')
                    ->onDelete('set null');
            }

            // Validaciones
            $table->check('producto_stock_minimo >= 0');
            $table->check('producto_stock_maximo >= 0');
        });

        // ========================
        // NUEVA TABLA: ASIGNACIÓN LICENCIA-PRODUCTO
        // ========================
        Schema::create('pro_licencia_asignacion_producto', function (Blueprint $table) {
            $table->id('asignacion_id');
            $table->unsignedBigInteger('asignacion_producto_id')->comment('FK al producto del inventario');
            $table->unsignedBigInteger('asignacion_licencia_id')->comment('FK a la licencia de importación');
            $table->integer('asignacion_cantidad')->comment('Cantidad de este producto en esta licencia');
            $table->integer('asignacion_situacion')->default(1)->comment('1 = activo, 0 = inactivo');
            $table->timestamps();

            // Índices
            $table->index('asignacion_producto_id');
            $table->index('asignacion_licencia_id');
            $table->index('asignacion_situacion');

            // Foreign Keys
            $table->foreign('asignacion_producto_id')
                ->references('producto_id')
                ->on('pro_productos')
                ->onDelete('cascade');

            $table->foreign('asignacion_licencia_id')
                ->references('lipaimp_id')
                ->on('pro_licencias_para_importacion')
                ->onDelete('cascade');

            // Validaciones
            $table->check('asignacion_cantidad > 0');

       
        });


        // Tabla de fotos de productos
        Schema::create('pro_productos_fotos', function (Blueprint $table) {
            $table->id('foto_id');
            $table->unsignedBigInteger('foto_producto_id');
            $table->string('foto_url', 255);
            $table->string('foto_alt_text', 255)->nullable()->comment('Texto alternativo para SEO/accesibilidad');
            $table->boolean('foto_principal')->default(false)->comment('TRUE si es la imagen destacada');
            $table->integer('foto_orden')->default(0)->comment('Orden de visualización');
            $table->integer('foto_situacion')->default(1)->comment('1 = activa, 0 = inactiva');
            $table->timestamp('created_at')->useCurrent()->comment('Fecha de subida');

            // Índices
            $table->index('foto_producto_id');
            $table->index('foto_principal');
            $table->index('foto_orden');
            $table->index(['foto_producto_id', 'foto_situacion']);

            // Clave foránea
            $table->foreign('foto_producto_id')
                ->references('producto_id')
                ->on('pro_productos')
                ->onDelete('cascade');
        });

        // Tabla de series individuales
        Schema::create('pro_series_productos', function (Blueprint $table) {
            $table->id('serie_id');
            $table->unsignedBigInteger('serie_producto_id');
            $table->unsignedBigInteger('serie_asignacion_id')->nullable()->comment('FK a la asignación licencia-producto si aplica');
            $table->string('serie_numero_serie', 200);
            $table->string('serie_estado', 25)->default('disponible');
            $table->timestamp('serie_fecha_ingreso')->useCurrent();
            $table->string('serie_observaciones', 255)->nullable();
            $table->integer('serie_situacion')->default(1);
            $table->timestamps();

            $table->index('serie_producto_id');
            $table->index('serie_asignacion_id');
            $table->index('serie_estado');
            $table->index('serie_numero_serie');

            $table->foreign('serie_producto_id')
                ->references('producto_id')
                ->on('pro_productos')
                ->onDelete('cascade');

            $table->foreign('serie_asignacion_id')
                ->references('asignacion_id')
                ->on('pro_licencia_asignacion_producto')
                ->onDelete('set null');
        });
        // Tabla de lotes de productos
        // Migración corregida para pro_lotes
        Schema::create('pro_lotes', function (Blueprint $table) {
            $table->id('lote_id');
            $table->string('lote_codigo', 100)->unique()->comment('Código único del lote, ej: L2025-08-GLOCK-001');

            // NUEVO: Relación con producto específico
            $table->unsignedBigInteger('lote_producto_id')->comment('FK al producto específico');

            $table->timestamp('lote_fecha')->useCurrent()->comment('Fecha de creación o ingreso del lote');
            $table->string('lote_descripcion', 255)->nullable()->comment('Descripción breve opcional del lote');

            // NUEVO: Cantidades del lote
            $table->integer('lote_cantidad_total')->default(0)->comment('Cantidad total en este lote');
            $table->integer('lote_cantidad_disponible')->default(0)->comment('Cantidad disponible en este lote');

            $table->unsignedBigInteger('lote_usuario_id')->nullable()->comment('Usuario que creó el lote');
            $table->integer('lote_situacion')->default(1)->comment('1 = activo, 0 = cerrado o eliminado');
            $table->timestamps();

            // Índices
            $table->index('lote_codigo');
            $table->index('lote_producto_id');
            $table->index('lote_fecha');
            $table->index('lote_cantidad_total');
            $table->index('lote_cantidad_disponible');
            $table->index('lote_usuario_id');
            $table->index('lote_situacion');

            // Foreign Keys
            $table->foreign('lote_producto_id')
                ->references('producto_id')
                ->on('pro_productos')
                ->onDelete('cascade');

            if (Schema::hasTable('users')) {
                $table->foreign('lote_usuario_id')
                    ->references('user_id')
                    ->on('users')
                    ->onDelete('set null');
            }

            // Constraints de validación
            $table->check('lote_cantidad_total >= 0');
            $table->check('lote_cantidad_disponible >= 0');
            $table->check('lote_cantidad_disponible <= lote_cantidad_total');
        });

        // Tabla de precios de productos
        Schema::create('pro_precios', function (Blueprint $table) {
            $table->id('precio_id');
            $table->unsignedBigInteger('precio_producto_id');
            $table->decimal('precio_costo', 10, 2)->comment('Precio de compra del producto');
            $table->decimal('precio_venta', 10, 2)->comment('Precio regular de venta');
            $table->decimal('precio_margen', 5, 2)->nullable()->comment('Margen de ganancia estimado (%)');
            $table->decimal('precio_especial', 10, 2)->nullable()->comment('Precio especial, si se aplica');
            $table->string('precio_moneda', 3)->default('GTQ')->comment('Código de moneda ISO');
            $table->string('precio_justificacion', 255)->nullable()->comment('Motivo del precio especial');
            $table->date('precio_fecha_asignacion')->comment('Fecha en que se asignó este precio');
            $table->unsignedBigInteger('precio_usuario_id')->nullable()->comment('Usuario que asignó el precio');
            $table->integer('precio_situacion')->default(1)->comment('1 = activo, 0 = histórico o inactivo');
            $table->timestamps();

            // Índices para consultas rápidas
            $table->index(['precio_producto_id', 'precio_fecha_asignacion']);
            $table->index('precio_situacion');
            $table->index('precio_usuario_id');
            $table->index('precio_producto_id');

            // Claves foráneas
            $table->foreign('precio_producto_id')
                ->references('producto_id')
                ->on('pro_productos')
                ->onDelete('cascade');

            if (Schema::hasTable('users')) {
                $table->foreign('precio_usuario_id')
                    ->references('user_id')
                    ->on('users')
                    ->onDelete('set null');
            }

            // Validaciones
            $table->check('precio_costo > 0');
            $table->check('precio_venta > 0');
            $table->check('precio_especial IS NULL OR precio_especial >= 0');
        });

        // Tabla de promociones temporales
        Schema::create('pro_promociones', function (Blueprint $table) {
            $table->id('promo_id');
            $table->unsignedBigInteger('promo_producto_id');
            $table->string('promo_nombre', 100)->comment('Nombre de la promoción, ej: Black Friday');
            $table->string('promo_tipo', 20)->default('porcentaje')->comment('porcentaje o fijo');
            $table->decimal('promo_valor', 10, 2)->comment('Valor del descuento');
            $table->decimal('promo_precio_original', 10, 2)->nullable()->comment('Precio antes del descuento');
            $table->decimal('promo_precio_descuento', 10, 2)->nullable()->comment('Precio final con descuento');
            $table->date('promo_fecha_inicio')->comment('Inicio de la promoción');
            $table->date('promo_fecha_fin')->comment('Fin de la promoción');
            $table->string('promo_justificacion', 255)->nullable()->comment('Motivo de la promoción');
            $table->unsignedBigInteger('promo_usuario_id')->nullable()->comment('Usuario que creó la promoción');
            $table->integer('promo_situacion')->default(1)->comment('1 = activa, 0 = expirada o desactivada');
            $table->timestamps();

            // Índices
            $table->index('promo_producto_id');
            $table->index(['promo_fecha_inicio', 'promo_fecha_fin']);
            $table->index('promo_situacion');

            // Clave foránea
            $table->foreign('promo_producto_id')
                ->references('producto_id')
                ->on('pro_productos')
                ->onDelete('cascade');

            if (Schema::hasTable('users')) {
                $table->foreign('promo_usuario_id')
                    ->references('user_id')
                    ->on('users')
                    ->onDelete('set null');
            }

            // Validaciones
            $table->check('promo_fecha_fin >= promo_fecha_inicio');
            $table->check('promo_valor > 0');
        });

        // Tabla de movimientos de inventario
        Schema::create('pro_movimientos', function (Blueprint $table) {
            $table->id('mov_id');
            $table->unsignedBigInteger('mov_producto_id');
            $table->string('mov_tipo', 50)->comment('ingreso, egreso, ajuste_positivo, ajuste_negativo, venta, devolucion, merma, transferencia');
            $table->string('mov_origen', 100)->nullable()->comment('Fuente del movimiento');
            $table->string('mov_destino', 100)->nullable()->comment('Destino del movimiento si aplica');
            $table->integer('mov_cantidad')->comment('Cantidad afectada por el movimiento');
            $table->decimal('mov_precio_unitario', 10, 2)->nullable()->comment('Precio unitario en el momento del movimiento');
            $table->decimal('mov_valor_total', 10, 2)->nullable()->comment('Valor total del movimiento');
            $table->timestamp('mov_fecha')->useCurrent()->comment('Fecha del movimiento');
            $table->unsignedBigInteger('mov_usuario_id')->comment('Usuario que realizó el movimiento');
            $table->unsignedBigInteger('mov_lote_id')->nullable()->comment('FK al lote si aplica');
            $table->unsignedBigInteger('mov_serie_id')->nullable()->comment('FK a la serie específica si aplica');
            $table->string('mov_documento_referencia', 100)->nullable()->comment('Número de factura, orden, etc.');
            $table->string('mov_observaciones', 250)->nullable()->comment('Detalles u observaciones del movimiento');
            $table->integer('mov_situacion')->default(1)->comment('1 = activo, 0 = anulado');
            $table->timestamps();

            // Índices para reportes y consultas frecuentes
            $table->index(['mov_producto_id', 'mov_fecha']);
            $table->index(['mov_tipo', 'mov_fecha']);
            $table->index(['mov_usuario_id', 'mov_fecha']);
            $table->index('mov_lote_id');
            $table->index('mov_serie_id');
            $table->index('mov_situacion');
            $table->index('mov_producto_id');
            $table->index('mov_tipo');
            $table->index('mov_fecha');
            $table->index('mov_usuario_id');

            // Claves foráneas
            $table->foreign('mov_producto_id')
                ->references('producto_id')
                ->on('pro_productos')
                ->onDelete('restrict');

            $table->foreign('mov_lote_id')
                ->references('lote_id')
                ->on('pro_lotes')
                ->onDelete('set null');

            $table->foreign('mov_serie_id')
                ->references('serie_id')
                ->on('pro_series_productos')
                ->onDelete('set null');

            if (Schema::hasTable('users')) {
                $table->foreign('mov_usuario_id')
                    ->references('user_id')
                    ->on('users')
                    ->onDelete('restrict');
            }

            // Validaciones
            $table->check('mov_precio_unitario IS NULL OR mov_precio_unitario >= 0');
        });

        // Tabla de stock actual
        Schema::create('pro_stock_actual', function (Blueprint $table) {
            $table->id('stock_id');
            $table->unsignedBigInteger('stock_producto_id');
            $table->integer('stock_cantidad_total')->default(0)->comment('Stock total del producto');
            $table->integer('stock_cantidad_disponible')->default(0)->comment('Stock disponible para venta');
            $table->integer('stock_cantidad_reservada')->default(0)->comment('Stock reservado/apartado');
            $table->decimal('stock_valor_total', 12, 2)->default(0)->comment('Valor total del inventario');
            $table->timestamp('stock_ultimo_movimiento')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Índices
            $table->index('stock_producto_id');
            $table->index('stock_cantidad_disponible');
            $table->unique('stock_producto_id'); // Un registro por producto

            // Clave foránea
            $table->foreign('stock_producto_id')
                ->references('producto_id')
                ->on('pro_productos')
                ->onDelete('cascade');

            // Validaciones
            $table->check('stock_cantidad_total >= 0');
            $table->check('stock_cantidad_disponible >= 0');
            $table->check('stock_cantidad_reservada >= 0');
        });

        // ========================
        // SISTEMA DE ALERTAS
        // ========================

        // Tabla principal de alertas
        Schema::create('pro_alertas', function (Blueprint $table) {
            $table->id('alerta_id');
            $table->string('alerta_tipo', 50)->comment('stock_bajo, stock_agotado, etc.');
            $table->string('alerta_titulo', 100)->comment('Título de la alerta');
            $table->text('alerta_mensaje')->comment('Mensaje detallado');
            $table->string('alerta_prioridad', 20)->default('media')->comment('baja, media, alta, critica');

            // Solo lo esencial
            $table->unsignedBigInteger('alerta_producto_id')->nullable()->comment('Producto relacionado si aplica');
            $table->unsignedBigInteger('alerta_usuario_id')->nullable()->comment('Usuario específico si aplica');

            // Campo para todos los roles
            $table->boolean('alerta_para_todos')->default(false)->comment('TRUE = todos los roles pueden verla, FALSE = solo roles específicos');

            // Control simple
            $table->boolean('alerta_vista')->default(false)->comment('Si ya fue vista');
            $table->boolean('alerta_resuelta')->default(false)->comment('Si fue resuelta');
            $table->timestamp('alerta_fecha')->useCurrent()->comment('Cuándo se generó');

            // Email simple
            $table->boolean('email_enviado')->default(false)->comment('Si se envió email');

            // Índices básicos
            $table->index('alerta_tipo');
            $table->index('alerta_vista');
            $table->index('alerta_producto_id');
            $table->index('alerta_para_todos');
            $table->index('alerta_prioridad');
            $table->index('alerta_resuelta');

            // Foreign Keys básicas
            $table->foreign('alerta_producto_id')
                ->references('producto_id')
                ->on('pro_productos')
                ->onDelete('cascade');

            if (Schema::hasTable('users')) {
                $table->foreign('alerta_usuario_id')
                    ->references('user_id')
                    ->on('users')
                    ->onDelete('set null');
            }
        });

        // Tabla de relación: alertas específicas por roles
        Schema::create('pro_alertas_roles', function (Blueprint $table) {
            $table->id('alerta_rol_id');
            $table->unsignedBigInteger('alerta_id');
            $table->unsignedInteger('rol_id');

            $table->foreign('alerta_id')
                ->references('alerta_id')
                ->on('pro_alertas')
                ->onDelete('cascade');

            if (Schema::hasTable('roles')) {
                $table->foreign('rol_id')
                    ->references('id')
                    ->on('roles')
                    ->onDelete('cascade');
            }

            $table->unique(['alerta_id', 'rol_id']);
        });
    }



    public function down()
    {
        // Eliminar en orden inverso respetando todas las dependencias de foreign keys

        // SISTEMA DE ALERTAS (más nuevas, eliminar primero)
        Schema::dropIfExists('pro_alertas_roles');
        Schema::dropIfExists('pro_alertas');

        // SISTEMA DE INVENTARIO NUEVO
        Schema::dropIfExists('pro_stock_actual');
        Schema::dropIfExists('pro_movimientos');
        Schema::dropIfExists('pro_promociones');
        Schema::dropIfExists('pro_precios');
        Schema::dropIfExists('pro_series_productos');
        Schema::dropIfExists('pro_productos_fotos');
        Schema::dropIfExists('pro_lotes');
        Schema::dropIfExists('pro_productos');

        // DOCUMENTACIÓN Y COMPROBANTES
        Schema::dropIfExists('pro_documentacion_lic_import');
        Schema::dropIfExists('pro_comprobantes_pago');
        Schema::dropIfExists('pro_comprobantes_pago_ventas');

        // PAGOS
        Schema::dropIfExists('pro_pagos');
        Schema::dropIfExists('pro_pagos_licencias');

        // VENTAS Y DETALLES
        Schema::dropIfExists('pro_detalle_venta');
        Schema::dropIfExists('pro_ventas');
        Schema::dropIfExists('pro_clientes');

        // INVENTARIO ANTIGUO
        Schema::dropIfExists('pro_inventario_armas');
        Schema::dropIfExists('pro_inventario_modelos');

        // ARMAS LICENCIADAS
        Schema::dropIfExists('pro_armas_licenciadas');

        // LICENCIAS Y EMPRESAS
        Schema::dropIfExists('pro_licencias_para_importacion');
        Schema::dropIfExists('pro_empresas_de_importacion');

        // CLASIFICACIONES (tienen dependencias entre ellas)
        Schema::dropIfExists('pro_subcategorias');
        Schema::dropIfExists('pro_categorias');
        Schema::dropIfExists('pro_calibres');
        Schema::dropIfExists('pro_modelo');
        Schema::dropIfExists('pro_marcas');

        // CATÁLOGOS BASE
        Schema::dropIfExists('pro_unidades_medida');
        Schema::dropIfExists('pro_paises');
        Schema::dropIfExists('pro_metodos_pago');

        Schema::dropIfExists('pro_comprobantes_pago_licencias');
        Schema::dropIfExists('pro_pagos_lic_metodos');
        Schema::dropIfExists('pro_pagos_licencias');
        Schema::dropIfExists('pro_licencias_total_pagado');
    }
};