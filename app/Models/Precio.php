<?php
// app/Models/Precio.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Precio extends Model
{
    protected $table = 'pro_precios';
    protected $primaryKey = 'precio_id';
    
    protected $fillable = [
        'precio_producto_id',
        'precio_costo',
        'precio_venta',
        'precio_margen',
        'precio_especial',
        'precio_justificacion',
        'precio_fecha_asignacion',
        'precio_situacion'
    ];

    protected $casts = [
        'precio_costo' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'precio_margen' => 'decimal:2',
        'precio_especial' => 'decimal:2',
        'precio_fecha_asignacion' => 'date',
        'precio_situacion' => 'integer'
    ];

    /**
     * Relación con el producto
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'precio_producto_id', 'producto_id');
    }

    /**
     * Scope para precios activos
     */
    public function scopeActivos($query)
    {
        return $query->where('precio_situacion', 1);
    }

    /**
     * Calcula el margen de ganancia automáticamente
     */
    public function calcularMargen()
    {
        if ($this->precio_costo > 0) {
            return (($this->precio_venta - $this->precio_costo) / $this->precio_costo) * 100;
        }
        return 0;
    }

    /**
     * Obtiene el precio efectivo (especial si existe, sino el regular)
     */
    public function getPrecioEfectivoAttribute()
    {
        return $this->precio_especial ?? $this->precio_venta;
    }

    /**
     * Verifica si tiene precio especial activo
     */
    public function getTienePrecioEspecialAttribute()
    {
        return !is_null($this->precio_especial) && $this->precio_especial > 0;
    }
}

