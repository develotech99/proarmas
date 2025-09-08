<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/**
 * Modelo para las series de productos
 */
class SerieProducto extends Model
{
    protected $table = 'pro_series_productos';
    protected $primaryKey = 'serie_id';
    public $timestamps = false;

    protected $fillable = [
        'serie_producto_id',
        'serie_numero_serie',
        'serie_estado',
        'serie_fecha_ingreso',
        'serie_situacion'
    ];

    protected $casts = [
        'serie_fecha_ingreso' => 'datetime',
        'serie_situacion' => 'integer'
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'serie_producto_id', 'producto_id');
    }

    /**
     * Scope para series disponibles
     */
    public function scopeDisponibles($query)
    {
        return $query->where('serie_estado', 'disponible')
                    ->where('serie_situacion', 1);
    }
}