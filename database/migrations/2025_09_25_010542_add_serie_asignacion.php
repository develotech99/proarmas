<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pro_licencia_asignacion_producto', function (Blueprint $table) {
            // Solo agregar campo para series
            $table->unsignedBigInteger('asignacion_serie_id')->nullable()->after('asignacion_cantidad')->comment('FK a la serie específica si aplica');
            $table->text('asignacion_observaciones')->nullable()->after('asignacion_serie_id')->comment('Observaciones de la asignación');
            
            // Índice
            $table->index('asignacion_serie_id');
            
            // Foreign key
            $table->foreign('asignacion_serie_id', 'fk_asignacion_serie')
                ->references('serie_id')
                ->on('pro_series_productos')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('pro_licencia_asignacion_producto', function (Blueprint $table) {
            $table->dropForeign('fk_asignacion_serie');
            $table->dropColumn(['asignacion_serie_id', 'asignacion_observaciones']);
        });
    }
};