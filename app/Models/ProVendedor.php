<?php
// app/Models/ProVendedor.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProVendedor extends Model
{
    use HasFactory;

    protected $table = 'pro_vendedores';
    protected $primaryKey = 'vend_vendedor_id';

    protected $fillable = [
        'vend_user_id',
        'vend_codigo',
        'vend_nombres',
        'vend_apellidos',
        'vend_comision_porcentaje',
        'vend_telefono',
        'vend_email',
        'vend_situacion',
        'vend_fecha_ingreso',
    ];

    protected $casts = [
        'vend_comision_porcentaje' => 'decimal:2',
        'vend_fecha_ingreso' => 'date',
        'vend_situacion' => 'boolean',
    ];

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(User::class, 'vend_user_id');
    }

    public function ventas()
    {
        return $this->hasMany(ProVentaPrincipal::class, 'vend_vendedor_id');
    }

    public function comisiones()
    {
        return $this->hasMany(ProComisionVendedor::class, 'vend_vendedor_id');
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('vend_situacion', 1);
    }

    // Mutators
    public function setVendCodigoAttribute($value)
    {
        $this->attributes['vend_codigo'] = strtoupper($value);
    }

    // Accessors
    public function getNombreCompletoAttribute()
    {
        return $this->vend_nombres . ' ' . $this->vend_apellidos;
    }

    public function getVentasActivasAttribute()
    {
        return $this->ventas()->where('vent_situacion', 1)->count();
    }
}