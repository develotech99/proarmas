<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo para la gestión de productos en el inventario de armería
 * Maneja la información básica de productos, sus fotos y series
 */
class Producto extends Model
{
    use HasFactory;

    protected $table = 'pro_productos';
    protected $primaryKey = 'producto_id';
    public $timestamps = false;

    protected $fillable = [
        'producto_nombre',
        'producto_codigo_barra',
        'producto_categoria_id',
        'producto_subcategoria_id',
        'producto_marca_id',
        'producto_modelo_id',
        'producto_calibre_id',
        'producto_requiere_serie',
        'producto_es_importado',
        'producto_id_licencia',
        'producto_situacion'
    ];

    protected $casts = [
        'producto_requiere_serie' => 'boolean',
        'producto_es_importado' => 'boolean',
        'producto_situacion' => 'integer'
    ];

    /**
     * Relación con las fotos del producto
     */
    public function fotos(): HasMany
    {
        return $this->hasMany(ProductoFoto::class, 'foto_producto_id', 'producto_id')
                    ->where('foto_situacion', 1);
    }

    /**
     * Obtiene la foto principal del producto
     */
    public function fotoPrincipal()
    {
        return $this->fotos()->where('foto_principal', true)->first();
    }

    /**
     * Relación con las series del producto
     */
    public function series(): HasMany
    {
        return $this->hasMany(SerieProducto::class, 'serie_producto_id', 'producto_id')
                    ->where('serie_situacion', 1);
    }

    /**
     * Series disponibles para venta/egreso
     */
    public function seriesDisponibles(): HasMany
    {
        return $this->series()->where('serie_estado', 'disponible');
    }

    /**
     * Relación con los movimientos del producto
     */
    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class, 'mov_producto_id', 'producto_id')
                    ->where('mov_situacion', 1);
    }

    /**
     * Calcula el stock actual del producto
     * Si requiere serie: cuenta series disponibles
     * Si no requiere serie: suma ingresos menos egresos
     */
    public function getStockActualAttribute()
    {
        if ($this->producto_requiere_serie) {
            return $this->seriesDisponibles()->count();
        }

        $ingresos = $this->movimientos()
            ->whereIn('mov_tipo', ['ingreso', 'importación'])
            ->sum('mov_cantidad');

        $egresos = $this->movimientos()
            ->whereIn('mov_tipo', ['egreso', 'baja'])
            ->sum('mov_cantidad');

        return $ingresos - $egresos;
    }

    /**
     * Scope para productos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('producto_situacion', 1);
    }

    /**
     * Verifica si el producto tiene stock disponible
     */
    public function tieneStock($cantidad = 1)
    {
        return $this->stock_actual >= $cantidad;
    }
}

