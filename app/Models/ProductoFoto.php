<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/**
 * Modelo para las fotos de productos
 */
class ProductoFoto extends Model
{
    protected $table = 'pro_productos_fotos';
    protected $primaryKey = 'foto_id';
    public $timestamps = false;

    protected $fillable = [
        'foto_producto_id',
        'foto_url',
        'foto_principal',
        'foto_situacion'
    ];

    protected $casts = [
        'foto_principal' => 'boolean',
        'foto_situacion' => 'integer'
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'foto_producto_id', 'producto_id');
    }
}
