<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProUnidadesMedidaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('pro_unidades_medida')->insert([
            // LONGITUD
            [
                'unidad_nombre' => 'milímetro',
                'unidad_abreviacion' => 'mm',
                'unidad_tipo' => 'longitud',
                'unidad_situacion' => 1,
            ],
            [
                'unidad_nombre' => 'pulgada',
                'unidad_abreviacion' => 'in',
                'unidad_tipo' => 'longitud',
                'unidad_situacion' => 1,
            ],
            [
                'unidad_nombre' => 'centímetro',
                'unidad_abreviacion' => 'cm',
                'unidad_tipo' => 'longitud',
                'unidad_situacion' => 1,
            ],

            // MASA / PESO
            [
                'unidad_nombre' => 'gramo',
                'unidad_abreviacion' => 'g',
                'unidad_tipo' => 'masa',
                'unidad_situacion' => 1,
            ],
            [
                'unidad_nombre' => 'grain',
                'unidad_abreviacion' => 'gr',
                'unidad_tipo' => 'masa',
                'unidad_situacion' => 1,
            ],

            // ENERGÍA
            [
                'unidad_nombre' => 'joule',
                'unidad_abreviacion' => 'J',
                'unidad_tipo' => 'energía',
                'unidad_situacion' => 1,
            ],
            [
                'unidad_nombre' => 'pie-libra fuerza',
                'unidad_abreviacion' => 'ft·lb',
                'unidad_tipo' => 'energía',
                'unidad_situacion' => 1,
            ],

            // VELOCIDAD
            [
                'unidad_nombre' => 'metros por segundo',
                'unidad_abreviacion' => 'm/s',
                'unidad_tipo' => 'velocidad',
                'unidad_situacion' => 1,
            ],
            [
                'unidad_nombre' => 'pies por segundo',
                'unidad_abreviacion' => 'ft/s',
                'unidad_tipo' => 'velocidad',
                'unidad_situacion' => 1,
            ],

            // PRESIÓN
            [
                'unidad_nombre' => 'libras por pulgada cuadrada',
                'unidad_abreviacion' => 'psi',
                'unidad_tipo' => 'presión',
                'unidad_situacion' => 1,
            ],
            [
                'unidad_nombre' => 'bar',
                'unidad_abreviacion' => 'bar',
                'unidad_tipo' => 'presión',
                'unidad_situacion' => 1,
            ],
        ]);
    }
}
