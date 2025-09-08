<?php
// app/Models/Promocion.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Promocion extends Model
{
    protected $table = 'pro_promociones';
    protected $primaryKey = 'promo_id';
    
    protected $fillable = [
        'promo_producto_id',
        'promo_nombre',
        'promo_tipo',
        'promo_valor',
        'promo_precio_original',
        'promo_precio_descuento',
        'promo_fecha_inicio',
        'promo_fecha_fin',
        'promo_justificacion',
        'promo_situacion'
    ];

    protected $casts = [
        'promo_valor' => 'decimal:2',
        'promo_precio_original' => 'decimal:2',
        'promo_precio_descuento' => 'decimal:2',
        'promo_fecha_inicio' => 'date',
        'promo_fecha_fin' => 'date',
        'promo_situacion' => 'integer'
    ];

    /**
     * Relación con el producto
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'promo_producto_id', 'producto_id');
    }

    /**
     * Scope para promociones activas
     */
    public function scopeActivas($query)
    {
        return $query->where('promo_situacion', 1)
                    ->where('promo_fecha_inicio', '<=', Carbon::now())
                    ->where('promo_fecha_fin', '>=', Carbon::now());
    }

    /**
     * Scope para promociones vigentes
     */
    public function scopeVigentes($query)
    {
        return $query->where('promo_situacion', 1);
    }

    /**
     * Verifica si la promoción está activa actualmente
     */
    public function getEstaActivaAttribute()
    {
        $now = Carbon::now();
        return $this->promo_situacion == 1 && 
               $now->between($this->promo_fecha_inicio, $this->promo_fecha_fin);
    }

    /**
     * Calcula el precio con descuento
     */
    public function calcularPrecioDescuento($precioOriginal)
    {
        if ($this->promo_tipo === 'porcentaje') {
            return $precioOriginal - ($precioOriginal * ($this->promo_valor / 100));
        } else {
            return $precioOriginal - $this->promo_valor;
        }
    }

    /**
     * Calcula el porcentaje de descuento
     */
    public function getPorcentajeDescuentoAttribute()
    {
        if ($this->promo_precio_original > 0 && $this->promo_precio_descuento > 0) {
            return (($this->promo_precio_original - $this->promo_precio_descuento) / $this->promo_precio_original) * 100;
        }
        return $this->promo_tipo === 'porcentaje' ? $this->promo_valor : 0;
    }

    /**
     * Obtiene el ahorro en dinero
     */
    public function getAhorroAttribute()
    {
        return $this->promo_precio_original - $this->promo_precio_descuento;
    }
}