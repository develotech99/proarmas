<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UsuarioAdmin1Seeder extends Seeder
{
    public function run(): void
    {
        // Insertar el rol "ADMIN"
        $rolId = DB::table('roles')->insertGetId([
            'nombre' => 'ADMIN',
            'descripcion' => 'Administrador full del sistema',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insertar primer usuario admin
        DB::table('users')->insert([
            'user_primer_nombre' => 'Administrador',
            'user_primer_apellido' => 'Sistema',
            'email' => 'develotech@outlook.es',
            'password' => Hash::make('admin123'),
            'user_rol' => $rolId,
            'user_fecha_verificacion' => now(),
            'remember_token' => Str::random(10),
            'user_situacion' => 1,
            'user_fecha_creacion' => now(),
        ]);

        // Insertar segundo usuario admin
        DB::table('users')->insert([
            'user_primer_nombre' => 'BRIAN',
            'user_segundo_nombre' => 'DANIEL',
            'user_primer_apellido' => 'MARIN',
            'email' => 'brianmarin4@gmail.com',
            'password' => Hash::make('admin123'),
            'user_rol' => $rolId,
            'user_fecha_verificacion' => now(),
            'remember_token' => Str::random(10),
            'user_situacion' => 1,
            'user_fecha_creacion' => now(),
        ]);
    }
}