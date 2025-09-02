<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ========================
        // Nuevas tablas
        // ========================

        // Tabla: pro_unidades_medida
        Schema::create('pro_unidades_medida', function (Blueprint $table) {
            $table->id('unidad_id');
            $table->string('unidad_nombre', 50);
            $table->string('unidad_abreviacion', 10);
            $table->string('unidad_tipo', 20)->default('longitud');
            $table->integer('unidad_situacion')->default(1);
        });

        // Tabla: pro_calibres
        Schema::create('pro_calibres', function (Blueprint $table) {
            $table->id('calibre_id');
            $table->string('calibre_nombre', 20);
            $table->unsignedBigInteger('calibre_unidad_id');
            $table->decimal('calibre_equivalente_mm', 6, 2)->nullable();
            $table->integer('calibre_situacion')->default(1);

            $table->foreign('calibre_unidad_id')->references('unidad_id')->on('pro_unidades_medida');
        });

        // ========================
        // ALTER a tabla existente
        // ========================

        Schema::table('pro_licencias_para_importacion', function (Blueprint $table) {
            // Solo agregamos campos si no existen aún
            if (!Schema::hasColumn('pro_licencias_para_importacion', 'lipaimp_clase')) {
                $table->unsignedBigInteger('lipaimp_clase')->nullable()->after('lipaimp_empresa');
                $table->foreign('lipaimp_clase')->references('clase_id')->on('pro_clases_pistolas');
            }

            if (!Schema::hasColumn('pro_licencias_para_importacion', 'lipaimp_marca')) {
                $table->unsignedBigInteger('lipaimp_marca')->nullable()->after('lipaimp_clase');
                $table->foreign('lipaimp_marca')->references('marca_id')->on('pro_marcas');
            }

            if (!Schema::hasColumn('pro_licencias_para_importacion', 'lipaimp_modelo')) {
                $table->unsignedBigInteger('lipaimp_modelo')->nullable()->after('lipaimp_marca');
                $table->foreign('lipaimp_modelo')->references('modelo_id')->on('pro_modelo');
            }
        });

        // ========================
        // Nueva tabla: armas licenciadas
        // ========================

        Schema::create('pro_armas_licenciadas', function (Blueprint $table) {
            $table->id('arma_id');
            $table->unsignedBigInteger('arma_licencia_id');
            $table->unsignedBigInteger('arma_clase_id');
            $table->unsignedBigInteger('arma_marca_id');
            $table->unsignedBigInteger('arma_modelo_id');
            $table->unsignedBigInteger('arma_calibre_id');
            $table->integer('arma_cantidad')->default(1);
            $table->integer('arma_situacion')->default(1);

            $table->foreign('arma_licencia_id')->references('lipaimp_id')->on('pro_licencias_para_importacion');
            $table->foreign('arma_clase_id')->references('clase_id')->on('pro_clases_pistolas');
            $table->foreign('arma_marca_id')->references('marca_id')->on('pro_marcas');
            $table->foreign('arma_modelo_id')->references('modelo_id')->on('pro_modelo');
            $table->foreign('arma_calibre_id')->references('calibre_id')->on('pro_calibres');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pro_armas_licenciadas');
        Schema::dropIfExists('pro_calibres');
        Schema::dropIfExists('pro_unidades_medida');

        // Si deseas revertir el ALTER TABLE (opcional)
        Schema::table('pro_licencias_para_importacion', function (Blueprint $table) {
            $table->dropForeign(['lipaimp_clase']);
            $table->dropForeign(['lipaimp_marca']);
            $table->dropForeign(['lipaimp_modelo']);
            $table->dropColumn(['lipaimp_clase', 'lipaimp_marca', 'lipaimp_modelo']);
        });
    }
};
