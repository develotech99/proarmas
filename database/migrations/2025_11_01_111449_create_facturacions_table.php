<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migración completa para sistema FEL Guatemala
     * Cumple con requisitos legales de SAT y optimización de almacenamiento
     * SE COMENTA CADA PARTE PARA QUE EL DEV PUEDA ENTENDER QUE ES LO QUE SE REALIZO
     * SE DEBE DE ALMACENAR EL XML RECIBIDO POR LA SAT DEBIDO QUE ESTA ESTABLECIDO EN la Resolución SAT 13-2018 y las normativas FEL de Guatemala
     * ATT. DEV CARLOS VASQUEZ
     */
    public function up(): void
    {
        Schema::create('facturacion', function (Blueprint $table) {
            $table->id('fac_id');

            // DATOS FEL
            $table->string('fac_uuid', 100)->nullable()->unique();
            $table->string('fac_referencia', 100)->unique();
            $table->string('fac_serie', 50)->nullable();
            $table->string('fac_numero', 50)->nullable();
            $table->enum('fac_estado', ['PENDIENTE', 'CERTIFICADO', 'ANULADO', 'ERROR'])->default('PENDIENTE');
            $table->string('fac_tipo_documento', 20)->default('FACT');

            // DATOS CLIENTE
            $table->string('fac_nit_receptor', 20);
            $table->string('fac_cui_receptor', 15)->nullable();
            $table->string('fac_receptor_nombre', 255)->nullable();
            $table->text('fac_receptor_direccion')->nullable();
            $table->string('fac_receptor_email', 150)->nullable();
            $table->string('fac_receptor_telefono', 20)->nullable();

            // FECHAS
            $table->date('fac_fecha_emision');
            $table->datetime('fac_fecha_certificacion')->nullable();
            $table->datetime('fac_fecha_anulacion')->nullable();

            // TOTALES
            $table->decimal('fac_subtotal', 12, 2);
            $table->decimal('fac_descuento', 12, 2)->default(0);
            $table->decimal('fac_impuestos', 12, 2)->default(0);
            $table->decimal('fac_total', 12, 2);
            $table->string('fac_moneda', 3)->default('GTQ');

            // RUTAS XML EN STORAGE
            $table->string('fac_xml_enviado_path', 500)->nullable();
            $table->string('fac_xml_certificado_path', 500)->nullable();
            $table->string('fac_xml_anulacion_path', 500)->nullable();

            // ANULACIÓN
            $table->string('fac_uuid_anulacion', 100)->nullable();
            $table->text('fac_motivo_anulacion')->nullable();

            // ERRORES Y ALERTAS
            $table->json('fac_errores')->nullable();
            $table->json('fac_alertas')->nullable();
            $table->text('fac_observaciones')->nullable();

            // INFORMACIÓN OPERATIVA
            $table->string('fac_operacion', 100);
            $table->string('fac_vendedor', 255)->nullable();
            $table->unsignedBigInteger('fac_usuario_id')->nullable();
            $table->dateTime('fac_fecha_operacion');

            // CONTROL DE IMPRESIÓN Y ENVÍO
            $table->integer('fac_veces_impreso')->default(0);
            $table->datetime('fac_fecha_primera_impresion')->nullable();
            $table->datetime('fac_fecha_ultima_impresion')->nullable();
            $table->boolean('fac_enviado_email')->default(false);
            $table->datetime('fac_fecha_envio_email')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('fac_usuario_id')->references('user_id')->on('users')->onDelete('set null');

            $table->index('fac_nit_receptor');
            $table->index('fac_fecha_emision');
            $table->index('fac_estado');
            $table->index('fac_tipo_documento');
            $table->index('fac_fecha_certificacion');
            $table->index(['fac_estado', 'fac_fecha_emision']);
        });

        Schema::create('facturacion_detalle', function (Blueprint $table) {
            $table->id('det_fac_id');
            $table->unsignedBigInteger('det_fac_factura_id');

            $table->string('det_fac_tipo', 10)->default('B');
            $table->string('det_fac_producto_codigo', 50)->nullable();
            $table->text('det_fac_producto_desc');

            $table->decimal('det_fac_cantidad', 12, 2);
            $table->string('det_fac_unidad_medida', 10)->default('UNI');
            $table->decimal('det_fac_precio_unitario', 12, 2);
            $table->decimal('det_fac_descuento', 12, 2)->default(0);

            $table->decimal('det_fac_monto_gravable', 12, 2);
            $table->string('det_fac_tipo_impuesto', 10)->default('IVA');
            $table->decimal('det_fac_impuesto', 12, 2)->default(0);

            $table->decimal('det_fac_total', 12, 2);

            $table->timestamps();

            $table->foreign('det_fac_factura_id')->references('fac_id')->on('facturacion')->onDelete('cascade');
            $table->index('det_fac_factura_id');
        });

        Schema::create('fel_tokens', function (Blueprint $table) {
            $table->id();
            $table->text('token');
            $table->string('token_type', 20)->default('bearer');
            $table->integer('expires_in');
            $table->datetime('issued_at');
            $table->datetime('expires_at');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('expires_at');
            $table->index('is_active');
        });

    }


    public function down(): void
    {
        Schema::dropIfExists('fel_tokens');
        Schema::dropIfExists('facturacion_detalle');
        Schema::dropIfExists('facturacion');
    }
};
