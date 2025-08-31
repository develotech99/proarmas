<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsuarioAdminSeeder extends Seeder
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

        // Insertar usuario admin
        DB::table('users')->insert([
            'name' => 'Administrador',
            'email' => 'admin@develotech.com',
            'password' => Hash::make('admin123'), // Hashea la password
            'rol_id' => $rolId,
            'email_verified_at' => now(),
            'remember_token' => \Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
