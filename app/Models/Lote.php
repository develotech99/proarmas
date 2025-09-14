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
    use HasFactory;
    
    protected $table = 'pro_lotes';
    protected $primaryKey = 'lote_id';
    public $timestamps = true; // Cambió a true porque agregamos timestamps

    protected $fillable = [
        'lote_codigo',
        'lote_fecha', 
        'lote_descripcion',
        'lote_usuario_id',      // NUEVO CAMPO
        'lote_situacion'
    ];

    protected $casts = [
        'lote_fecha' => 'datetime',
        'lote_situacion' => 'integer'
    ];

    /**
     * Relación con los movimientos del lote
     */
    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class, 'mov_lote_id', 'lote_id')
                    ->where('mov_situacion', 1);
    }

    /**
     * Relación con el usuario que creó el lote
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lote_usuario_id', 'user_id');
    }

    /**
     * Scope para lotes activos
     */
    public function scopeActivos($query)
    {
        return $query->where('lote_situacion', 1);
    }

    /**
     * Scope para lotes por fecha
     */
    public function scopePorFecha($query, $fechaInicio, $fechaFin = null)
    {
        if ($fechaFin) {
            return $query->whereBetween('lote_fecha', [$fechaInicio, $fechaFin]);
        }
        return $query->whereDate('lote_fecha', $fechaInicio);
    }

    /**
     * Scope para lotes recientes
     */
    public function scopeRecientes($query, $dias = 30)
    {
        return $query->where('lote_fecha', '>=', now()->subDays($dias));
    }

    /**
     * Calcula la cantidad total de productos en el lote
     */
    public function getCantidadTotalAttribute()
    {
        return $this->movimientos()
                    ->whereIn('mov_tipo', ['ingreso', 'ajuste_positivo'])
                    ->sum('mov_cantidad');
    }

    /**
     * Calcula la cantidad disponible del lote
     */
    public function getCantidadDisponibleAttribute()
    {
        $ingresos = $this->movimientos()
                         ->whereIn('mov_tipo', ['ingreso', 'ajuste_positivo'])
                         ->sum('mov_cantidad');

        $egresos = $this->movimientos()
                        ->whereIn('mov_tipo', ['egreso', 'venta', 'ajuste_negativo', 'merma'])
                        ->sum('mov_cantidad');

        return max(0, $ingresos - $egresos);
    }

    /**
     * Verifica si el lote está agotado
     */
    public function getEstaAgotadoAttribute()
    {
        return $this->cantidad_disponible <= 0;
    }

    /**
     * Obtiene los productos únicos en este lote
     */
    public function getProductosAttribute()
    {
        return $this->movimientos()
                    ->with('producto')
                    ->get()
                    ->pluck('producto')
                    ->unique('producto_id');
    }

    /**
     * Obtiene el primer movimiento (ingreso) del lote
     */
    public function getMovimientoOrigenAttribute()
    {
        return $this->movimientos()
                    ->where('mov_tipo', 'ingreso')
                    ->oldest('mov_fecha')
                    ->first();
    }

    /**
     * Obtiene el último movimiento del lote
     */
    public function getUltimoMovimientoAttribute()
    {
        return $this->movimientos()
                    ->latest('mov_fecha')
                    ->first();
    }

    /**
     * Calcula el valor total del lote
     */
    public function getValorTotalAttribute()
    {
        return $this->movimientos()
                    ->whereNotNull('mov_valor_total')
                    ->sum('mov_valor_total');
    }

    /**
     * Obtiene el número de días desde la creación del lote
     */
    public function getDiasCreacionAttribute()
    {
        return $this->lote_fecha->diffInDays(now());
    }

    /**
     * Verifica si el lote tiene movimientos
     */
    public function tieneMovimientos()
    {
        return $this->movimientos()->exists();
    }

    /**
     * Genera un código de lote automático
     */
    public static function generarCodigo($prefijo = 'L', $fecha = null)
    {
        $fecha = $fecha ? carbon($fecha) : now();
        $base = $prefijo . $fecha->format('Y-m');
        
        // Buscar el último lote del mes
        $ultimo = static::where('lote_codigo', 'LIKE', $base . '-%')
                        ->latest('lote_id')
                        ->first();
        
        $numero = 1;
        if ($ultimo) {
            $partes = explode('-', $ultimo->lote_codigo);
            $numero = intval(end($partes)) + 1;
        }
        
        return $base . '-' . str_pad($numero, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Cierra el lote (lo marca como inactivo)
     */
    public function cerrar()
    {
        $this->update(['lote_situacion' => 0]);
    }

    /**
     * Verifica si se puede cerrar el lote
     */
    public function puedecerrarse()
    {
        // No se puede cerrar si aún tiene stock disponible
        return $this->cantidad_disponible <= 0;
    }

    /**
     * Hook para generar código automáticamente si no se proporciona
     */
    protected static function booted()
    {
        static::creating(function ($lote) {
            if (empty($lote->lote_codigo)) {
                $lote->lote_codigo = static::generarCodigo();
            }
            
            if (empty($lote->lote_fecha)) {
                $lote->lote_fecha = now();
            }
        });
    }
}