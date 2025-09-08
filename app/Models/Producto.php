<?php
// app/Models/Producto.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
     * Relación con los precios del producto
     */
    public function precios(): HasMany
    {
        return $this->hasMany(Precio::class, 'precio_producto_id', 'producto_id');
    }

    /**
     * Obtiene el precio actual del producto
     */
    public function precioActual()
    {
        return $this->precios()->activos()->latest('precio_fecha_asignacion')->first();
    }

    /**
     * Relación con las promociones del producto
     */
    public function promociones(): HasMany
    {
        return $this->hasMany(Promocion::class, 'promo_producto_id', 'producto_id');
    }

    /**
     * Obtiene las promociones activas del producto
     */
    public function promocionesActivas(): HasMany
    {
        return $this->promociones()->activas();
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
            ->whereIn('mov_tipo', ['ingreso', 'importacion'])
            ->sum('mov_cantidad');

        $egresos = $this->movimientos()
            ->whereIn('mov_tipo', ['egreso', 'baja'])
            ->sum('mov_cantidad');

        return $ingresos - $egresos;
    }

    /**
     * Obtiene el precio de venta efectivo (con promoción si aplica)
     */
    public function getPrecioVentaEfectivoAttribute()
    {
        $precioActual = $this->precioActual();
        if (!$precioActual) {
            return 0;
        }

        // Verificar si tiene promoción activa
        $promocionActiva = $this->promocionesActivas()->first();
        if ($promocionActiva) {
            return $promocionActiva->promo_precio_descuento;
        }

        // Si tiene precio especial, usarlo, sino el precio regular
        return $precioActual->precio_especial ?? $precioActual->precio_venta;
    }

    /**
     * Obtiene el precio de costo actual
     */
    public function getPrecioCostoActualAttribute()
    {
        $precioActual = $this->precioActual();
        return $precioActual ? $precioActual->precio_costo : 0;
    }

    /**
     * Calcula el margen de ganancia actual
     */
    public function getMargenGananciaAttribute()
    {
        $precioActual = $this->precioActual();
        if (!$precioActual || $precioActual->precio_costo <= 0) {
            return 0;
        }

        $precioVenta = $this->precio_venta_efectivo;
        return (($precioVenta - $precioActual->precio_costo) / $precioActual->precio_costo) * 100;
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

    /**
     * Verifica si el producto tiene promoción activa
     */
    public function getTienePromocionActivaAttribute()
    {
        return $this->promocionesActivas()->exists();
    }

    /**
     * Obtiene el estado del stock (normal, bajo, agotado)
     */
    public function getEstadoStockAttribute()
    {
        $stock = $this->stock_actual;
        
        if ($stock === 0) {
            return 'agotado';
        } elseif ($stock <= 5) {
            return 'bajo';
        } else {
            return 'normal';
        }
    }

    /**
     * Relaciones con tablas maestras
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo('App\Models\Categoria', 'producto_categoria_id', 'categoria_id');
    }

    public function subcategoria(): BelongsTo
    {
        return $this->belongsTo('App\Models\Subcategoria', 'producto_subcategoria_id', 'subcategoria_id');
    }

    public function marca(): BelongsTo
    {
        return $this->belongsTo('App\Models\Marca', 'producto_marca_id', 'marca_id');
    }

    public function modelo(): BelongsTo
    {
        return $this->belongsTo('App\Models\Modelo', 'producto_modelo_id', 'modelo_id');
    }

    public function calibre(): BelongsTo
    {
        return $this->belongsTo('App\Models\Calibre', 'producto_calibre_id', 'calibre_id');
    }
}