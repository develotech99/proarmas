<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo para los lotes de productos
 */
class Lote extends Model
{
    protected $table = 'pro_lotes';
    protected $primaryKey = 'lote_id';
    public $timestamps = false;

    protected $fillable = [
        'lote_codigo',
        'lote_fecha',
        'lote_descripcion',
        'lote_situacion'
    ];

    protected $casts = [
        'lote_fecha' => 'datetime',
        'lote_situacion' => 'integer'
    ];

    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class, 'mov_lote_id', 'lote_id');
    }
}
