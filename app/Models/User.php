<?php

// app/Models/User.php
namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'user_id';
    public $incrementing = true;
    protected $keyType = 'int';
     public $timestamps = false;

    // Si NO tienes updated_at, desactiva timestamps o mapea created_at
    const CREATED_AT = 'user_fecha_creacion';
    const UPDATED_AT = null; // o 'user_fecha_actualizacion' si existiera

    protected $fillable = [
        'user_primer_nombre',
        'user_segundo_nombre',
        'user_primer_apellido',
        'user_segundo_apellido',
        'email',
        'password',
        'user_dpi_dni',
        'user_rol',
        'user_foto',
        'user_token',
        'user_fecha_verificacion',
        'user_situacion',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'user_fecha_verificacion' => 'datetime',
            'user_fecha_creacion'     => 'datetime',
        ];
    }

    // === Accessors para compatibilidad con Blade y Auth ===

    // Nombre completo virtual: $user->name
    protected function name(): Attribute
    {
        return Attribute::get(function () {
            return trim(
                implode(' ', array_filter([
                    $this->user_primer_nombre,
                    $this->user_segundo_nombre,
                    $this->user_primer_apellido,
                    $this->user_segundo_apellido,
                ]))
            );
        });
    }

    // Email virtual: $user->email
    protected function email(): Attribute
    {
        return Attribute::get(fn() => $this->email)
            ->set(fn($value) => $this->attributes['email'] = $value);
    }

    // Password virtual para Auth: $user->password
    protected function password(): Attribute
    {
        return Attribute::get(fn() => $this->password)
            ->set(fn($value) => $this->attributes['password'] = $value);
    }

    // created_at virtual para que la vista use ->created_at
    protected function createdAt(): Attribute
    {
        return Attribute::get(fn() => $this->user_fecha_creacion);
    }

    // Relación con roles (FK: user_rol -> roles.id)
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'user_rol', 'id');
    }
}
