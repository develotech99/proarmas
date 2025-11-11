<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProCalibresSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('pro_calibres')->insert([
            // MILÍMETROS (unidad_id = 1)
            [
                'calibre_nombre' => '9',
                'calibre_unidad_id' => 1,
                'calibre_equivalente_mm' => 9.00,
                'calibre_situacion' => 1,
            ],
            [
                'calibre_nombre' => '5.56',
                'calibre_unidad_id' => 1,
                'calibre_equivalente_mm' => 5.56,
                'calibre_situacion' => 1,
            ],
            [
                'calibre_nombre' => '7.62',
                'calibre_unidad_id' => 1,
                'calibre_equivalente_mm' => 7.62,
                'calibre_situacion' => 1,
            ],
            [
                'calibre_nombre' => '12.7',
                'calibre_unidad_id' => 1,
                'calibre_equivalente_mm' => 12.70,
                'calibre_situacion' => 1,
            ],
            [
                'calibre_nombre' => '10',
                'calibre_unidad_id' => 1,
                'calibre_equivalente_mm' => 10.00,
                'calibre_situacion' => 1,
            ],
            [
                'calibre_nombre' => '.380',
                'calibre_unidad_id' => 1,
                'calibre_equivalente_mm' => 9.65,
                'calibre_situacion' => 1,
            ],

            // PULGADAS (unidad_id = 2)
            [
                'calibre_nombre' => '.22',
                'calibre_unidad_id' => 2,
                'calibre_equivalente_mm' => 5.59,
                'calibre_situacion' => 1,
            ],
            [
                'calibre_nombre' => '.45',
                'calibre_unidad_id' => 2,
                'calibre_equivalente_mm' => 11.43,
                'calibre_situacion' => 1,
            ],
            [
                'calibre_nombre' => '.50',
                'calibre_unidad_id' => 2,
                'calibre_equivalente_mm' => 12.70,
                'calibre_situacion' => 1,
            ],
            [
                'calibre_nombre' => '.308',
                'calibre_unidad_id' => 2,
                'calibre_equivalente_mm' => 7.82,
                'calibre_situacion' => 1,
            ],
        ]);
    }
}
