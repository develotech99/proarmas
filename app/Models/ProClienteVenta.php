<?php

// app/Models/ProClienteVenta.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProClienteVenta extends Model
{
    use HasFactory;

    protected $table = 'pro_clientes_ventas';
    protected $primaryKey = 'clie_cliente_id';

    protected $fillable = [
        'clie_tipo',
        'clie_codigo',
        'clie_nombre',
        'clie_nombre_comercial',
        'clie_razon_social',
        'clie_nit',
        'clie_dpi',
        'clie_telefono',
        'clie_email',
        'clie_direccion',
        'clie_ubicacion',
        'clie_situacion',
    ];

    protected $casts = [
        'clie_situacion' => 'boolean',
    ];

    // Relaciones
    public function ventas()
    {
        return $this->hasMany(ProVentaPrincipal::class, 'clie_cliente_id');
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('clie_situacion', 1);
    }

    public function scopeEmpresas($query)
    {
        return $query->where('clie_tipo', 'empresa');
    }

    public function scopePersonas($query)
    {
        return $query->where('clie_tipo', 'persona');
    }

    // Accessors
    public function getNombreDisplayAttribute()
    {
        if ($this->clie_tipo === 'empresa') {
            return $this->clie_nombre_comercial ?: $this->clie_razon_social ?: $this->clie_nombre;
        }
        return $this->clie_nombre;
    }

    public function getDocumentoAttribute()
    {
        return $this->clie_tipo === 'empresa' ? $this->clie_nit : $this->clie_dpi;
    }
}