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
        // Agregar timestamps a pro_unidades_medida
        Schema::table('pro_unidades_medida', function (Blueprint $table) {
            if (!Schema::hasColumn('pro_unidades_medida', 'created_at')) {
                $table->timestamps();
            }
        });

        // Agregar timestamps a pro_calibres
        Schema::table('pro_calibres', function (Blueprint $table) {
            if (!Schema::hasColumn('pro_calibres', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover timestamps de pro_unidades_medida
        Schema::table('pro_unidades_medida', function (Blueprint $table) {
            $table->dropTimestamps();
        });

        // Remover timestamps de pro_calibres
        Schema::table('pro_calibres', function (Blueprint $table) {
            $table->dropTimestamps();
        });
    }
};