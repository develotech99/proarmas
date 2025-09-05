<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pro_categorias', function (Blueprint $table) {
            $table->id('categoria_id');
            $table->string('categoria_nombre', 100);
            $table->integer('categoria_situacion')->default(1);
            $table->timestamps(); // opcional
        });

        Schema::create('pro_subcategorias', function (Blueprint $table) {
            $table->id('subcategoria_id');
            $table->string('subcategoria_nombre', 100);
            $table->unsignedBigInteger('subcategoria_idcategoria');
            $table->integer('subcategoria_situacion')->default(1);
            $table->foreign('subcategoria_idcategoria')
                  ->references('categoria_id')
                  ->on('pro_categorias')
                  ->onDelete('cascade');
            $table->timestamps(); // opcional
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pro_subcategorias');
        Schema::dropIfExists('pro_categorias');
    }
};
