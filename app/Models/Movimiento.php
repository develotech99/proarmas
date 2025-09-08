<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/**
 * Modelo para los movimientos de inventario
 */
class Movimiento extends Model
{
    protected $table = 'pro_movimientos';
    protected $primaryKey = 'mov_id';
    public $timestamps = false;

    protected $fillable = [
        'mov_producto_id',
        'mov_tipo',
        'mov_origen',
        'mov_cantidad',
        'mov_fecha',
        'mov_usuario_id',
        'mov_lote_id',
        'mov_observaciones',
        'mov_situacion'
    ];

    protected $casts = [
        'mov_fecha' => 'datetime',
        'mov_cantidad' => 'integer',
        'mov_usuario_id' => 'integer',
        'mov_lote_id' => 'integer',
        'mov_situacion' => 'integer'
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'mov_producto_id', 'producto_id');
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'mov_lote_id', 'lote_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'mov_usuario_id', 'id');
    }

    /**
     * Scope para movimientos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('mov_situacion', 1);
    }

    /**
     * Scope por tipo de movimiento
     */
    public function scopeTipo($query, $tipo)
    {
        return $query->where('mov_tipo', $tipo);
    }
}