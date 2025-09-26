<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pro_alertas', function (Blueprint $table) {
            $table->timestamp('email_fecha_envio')
                  ->nullable()
                  ->after('email_enviado')
                  ->comment('Última vez que se envió email');
        });
    }

    public function down(): void
    {
        Schema::table('pro_alertas', function (Blueprint $table) {
            $table->dropColumn('email_fecha_envio');
        });
    }
};