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

        // Clases de pistolas
        Schema::create('pro_clases_pistolas', function (Blueprint $table) {
            $table->id('clase_id')->comment('ID de clase de arma');
            $table->string('clase_descripcion', 50)->nullable()->comment('pistola, carabina, etc.');
            $table->integer('clase_situacion')->default(1)->comment('1 = activo, 0 = inactivo');
            $table->timestamps();
        });

        // Marcas
        Schema::create('pro_marcas', function (Blueprint $table) {
            $table->id('marca_id')->comment('ID de marca');
            $table->string('marca_descripcion', 50)->nullable()->comment('system defense, glock, brigade');
            $table->integer('marca_situacion')->default(1)->comment('1 = activa, 0 = inactiva');
            $table->timestamps();
        });

        // Modelos
        Schema::create('pro_modelo', function (Blueprint $table) {
            $table->id('modelo_id')->comment('ID de modelo');
            $table->string('modelo_descripcion', 50)->nullable()->comment('c9, bm-f-9, sd15');
            $table->integer('modelo_situacion')->default(1)->comment('1 = activo, 0 = inactivo');
            $table->timestamps();
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
        Schema::create('pro_licencias_para_importacion', function (Blueprint $table) {
            $table->id('lipaimp_id')->comment('ID de licencia de importación');
            $table->integer('lipaimp_poliza')->nullable()->comment('número de póliza o factura');
            $table->string('lipaimp_descripcion', 100)->nullable()->comment('Descripción identificativa de la licencia');
            $table->unsignedBigInteger('lipaimp_empresa')->comment('Empresa asignada a la licencia');
            $table->unsignedBigInteger('lipaimp_clase')->nullable()->comment('Clase de arma');
            $table->unsignedBigInteger('lipaimp_marca')->nullable()->comment('Marca de arma');
            $table->unsignedBigInteger('lipaimp_modelo')->nullable()->comment('Modelo de arma');
            $table->unsignedBigInteger('lipaimp_calibre')->nullable()->comment('Calibre de arma');
            $table->date('lipaimp_fecha_vencimiento')->nullable()->comment('Fecha de vencimiento de la licencia');
            $table->integer('lipaimp_situacion')->default(1)->comment('1 = activa, 0 = inactiva');
            $table->timestamps();
            
            $table->foreign('lipaimp_empresa')->references('empresaimp_id')->on('pro_empresas_de_importacion');
            $table->foreign('lipaimp_clase')->references('clase_id')->on('pro_clases_pistolas');
            $table->foreign('lipaimp_marca')->references('marca_id')->on('pro_marcas');
            $table->foreign('lipaimp_modelo')->references('modelo_id')->on('pro_modelo');
            $table->foreign('lipaimp_calibre')->references('calibre_id')->on('pro_calibres');
        });

        // Digecam
        Schema::create('pro_digecam', function (Blueprint $table) {
            $table->id('digecam_id')->comment('ID digecam');
            $table->unsignedBigInteger('digecam_licencia_import')->comment('Licencia asociada');
            $table->string('digecam_autorizacion', 50)->default('no aprobada')->comment('Estado autorización');
            $table->integer('digecam_situacion')->default(1)->comment('1 = activa, 0 = inactiva');
            $table->timestamps();
            
            $table->foreign('digecam_licencia_import')->references('lipaimp_id')->on('pro_licencias_para_importacion');
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
            $table->unsignedBigInteger('modelo_clase');
            $table->unsignedBigInteger('modelo_marca');
            $table->unsignedBigInteger('modelo_modelo');
            $table->unsignedBigInteger('modelo_calibre')->nullable();
            $table->integer('modelo_cantidad')->default(0)->comment('Cantidad total en este lote');
            $table->integer('modelo_disponible')->default(0)->comment('Stock disponible');
            $table->integer('modelo_situacion')->default(1)->comment('1 = activo, 0 = inactivo');
            $table->timestamps();
            
            $table->foreign('modelo_licencia')->references('lipaimp_id')->on('pro_licencias_para_importacion');
            $table->foreign('modelo_clase')->references('clase_id')->on('pro_clases_pistolas');
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
            $table->unsignedBigInteger('arma_licencia_id');
            $table->unsignedBigInteger('arma_clase_id');
            $table->unsignedBigInteger('arma_marca_id');
            $table->unsignedBigInteger('arma_modelo_id');
            $table->unsignedBigInteger('arma_calibre_id');
            $table->integer('arma_cantidad')->default(1);
            $table->integer('arma_situacion')->default(1);
            $table->timestamps();

            $table->foreign('arma_licencia_id')->references('lipaimp_id')->on('pro_licencias_para_importacion');
            $table->foreign('arma_clase_id')->references('clase_id')->on('pro_clases_pistolas');
            $table->foreign('arma_marca_id')->references('marca_id')->on('pro_marcas');
            $table->foreign('arma_modelo_id')->references('modelo_id')->on('pro_modelo');
            $table->foreign('arma_calibre_id')->references('calibre_id')->on('pro_calibres');
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
        // ========================

        // Lotes
        Schema::create('pro_lotes', function (Blueprint $table) {
            $table->id('lote_id');
            $table->string('lote_codigo', 100)->unique()->comment('Ej: L2025-08-GLOCK-001');
            $table->timestamp('lote_fecha')->useCurrent();
            $table->string('lote_descripcion', 255)->nullable();
            $table->integer('lote_situacion')->default(1);
            $table->timestamps();
        });

        // Productos
        Schema::create('pro_productos', function (Blueprint $table) {
            $table->id('producto_id');
            $table->string('producto_nombre', 100);
            $table->string('producto_codigo_barra', 100)->unique()->nullable();
            $table->unsignedBigInteger('producto_categoria_id');
            $table->unsignedBigInteger('producto_subcategoria_id');
            $table->unsignedBigInteger('producto_marca_id');
            $table->unsignedBigInteger('producto_modelo_id')->nullable();
            $table->unsignedBigInteger('producto_calibre_id')->nullable();
            $table->boolean('producto_requiere_serie')->default(false);
            $table->boolean('producto_es_importado')->default(false);
            $table->unsignedBigInteger('producto_id_licencia')->nullable();
            $table->integer('producto_situacion')->default(1);
            $table->timestamps();

            $table->foreign('producto_categoria_id')->references('categoria_id')->on('pro_categorias');
            $table->foreign('producto_subcategoria_id')->references('subcategoria_id')->on('pro_subcategorias');
            $table->foreign('producto_marca_id')->references('marca_id')->on('pro_marcas');
            $table->foreign('producto_modelo_id')->references('modelo_id')->on('pro_modelo');
            $table->foreign('producto_calibre_id')->references('calibre_id')->on('pro_calibres');
            $table->foreign('producto_id_licencia')->references('lipaimp_id')->on('pro_licencias_para_importacion');
        });

        // Series de productos
        Schema::create('pro_series_productos', function (Blueprint $table) {
            $table->id('serie_id');
            $table->unsignedBigInteger('serie_producto_id');
            $table->string('serie_numero_serie', 200)->unique();
            $table->enum('serie_estado', ['disponible','reservado','vendido','baja'])->default('disponible');
            $table->timestamp('serie_fecha_ingreso')->useCurrent();
            $table->integer('serie_situacion')->default(1);
            $table->timestamps();

            $table->foreign('serie_producto_id')->references('producto_id')->on('pro_productos');
        });

        // Movimientos
        Schema::create('pro_movimientos', function (Blueprint $table) {
            $table->id('mov_id');
            $table->unsignedBigInteger('mov_producto_id');
            $table->string('mov_tipo', 50)->comment('ingreso, egreso, ajuste, etc.');
            $table->string('mov_origen', 100)->nullable()->comment('importación, ajuste, venta, compra local, etc.');
            $table->integer('mov_cantidad');
            $table->timestamp('mov_fecha')->useCurrent();
            $table->unsignedBigInteger('mov_usuario_id');
            $table->unsignedBigInteger('mov_lote_id')->nullable();
            $table->string('mov_observaciones', 250)->nullable();
            $table->integer('mov_situacion')->default(1);
            $table->timestamps();

            $table->foreign('mov_producto_id')->references('producto_id')->on('pro_productos');
            $table->foreign('mov_lote_id')->references('lote_id')->on('pro_lotes');
        });

        // Fotos de productos
        Schema::create('pro_productos_fotos', function (Blueprint $table) {
            $table->id('foto_id');
            $table->unsignedBigInteger('foto_producto_id');
            $table->string('foto_url', 255);
            $table->boolean('foto_principal')->default(false);
            $table->integer('foto_situacion')->default(1);
            $table->timestamps();

            $table->foreign('foto_producto_id')->references('producto_id')->on('pro_productos');
        });
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
        Schema::dropIfExists('pro_clases_pistolas');
        Schema::dropIfExists('pro_paises');
        Schema::dropIfExists('pro_metodos_pago');
    }
};