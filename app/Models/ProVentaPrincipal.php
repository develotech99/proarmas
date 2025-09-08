<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProVentaPrincipal extends Model
{
    use HasFactory;

    protected $table = 'pro_ventas_principales';
    protected $primaryKey = 'vent_venta_id';

    protected $fillable = [
        'vent_codigo',
        'vent_tipo',
        'vent_fecha',
        'clie_cliente_id',
        'vent_cliente_nombre_temporal',
        'vent_cliente_nit_temporal',
        'vent_cliente_telefono_temporal',
        'vent_cliente_direccion_temporal',
        'vend_vendedor_id',
        'vent_estado',
        'vent_estado_pago',
        'vent_subtotal',
        'vent_descuento_global',
        'vent_impuestos',
        'vent_total',
        'vent_total_pagado',
        'vent_saldo_pendiente',
        'vent_fecha_entrega',
        'vent_fecha_confirmacion',
        'vent_fecha_completado',
        'vent_observaciones',
        'vent_motivo_cancelacion',
        'vent_situacion',
        'vent_usuario_creacion',
        'vent_usuario_modificacion',
    ];

    protected $casts = [
        'vent_fecha' => 'datetime',
        'vent_fecha_entrega' => 'date',
        'vent_fecha_confirmacion' => 'datetime',
        'vent_fecha_completado' => 'datetime',
        'vent_subtotal' => 'decimal:2',
        'vent_descuento_global' => 'decimal:2',
        'vent_impuestos' => 'decimal:2',
        'vent_total' => 'decimal:2',
        'vent_total_pagado' => 'decimal:2',
        'vent_saldo_pendiente' => 'decimal:2',
        'vent_situacion' => 'boolean',
    ];

    // Relaciones
    public function cliente()
    {
        return $this->belongsTo(ProClienteVenta::class, 'clie_cliente_id', 'clie_cliente_id');
    }

    public function vendedor()
    {
        return $this->belongsTo(ProVendedor::class, 'vend_vendedor_id', 'vend_vendedor_id');
    }

    public function usuarioCreacion()
    {
        return $this->belongsTo(User::class, 'vent_usuario_creacion');
    }

    public function usuarioModificacion()
    {
        return $this->belongsTo(User::class, 'vent_usuario_modificacion');
    }

    public function detalles()
    {
        return $this->hasMany(ProDetalleVenta::class, 'vent_venta_id', 'vent_venta_id');
    }

    public function pagos()
    {
        return $this->hasMany(ProPagoVenta::class, 'vent_venta_id', 'vent_venta_id');
    }

    public function comisiones()
    {
        return $this->hasMany(ProComisionVendedor::class, 'vent_venta_id', 'vent_venta_id');
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('vent_situacion', 1);
    }

    public function scopeCotizaciones($query)
    {
        return $query->where('vent_tipo', 'cotizacion');
    }

    public function scopeVentas($query)
    {
        return $query->where('vent_tipo', 'venta');
    }

    public function scopePorEstado($query, $estado)
    {
        return $query->where('vent_estado', $estado);
    }

    public function scopePendientesPago($query)
    {
        return $query->whereIn('vent_estado_pago', ['pendiente', 'parcial']);
    }

    // Accessors
    public function getClienteNombreAttribute()
    {
        return $this->cliente ? $this->cliente->nombre_display : $this->vent_cliente_nombre_temporal;
    }

    public function getClienteDocumentoAttribute()
    {
        return $this->cliente ? $this->cliente->documento : $this->vent_cliente_nit_temporal;
    }

    public function getEstadoBadgeAttribute()
    {
        $badges = [
            'borrador' => ['class' => 'bg-gray-100 text-gray-800', 'text' => 'Borrador'],
            'cotizado' => ['class' => 'bg-blue-100 text-blue-800', 'text' => 'Cotizado'],
            'confirmado' => ['class' => 'bg-yellow-100 text-yellow-800', 'text' => 'Confirmado'],
            'entregado' => ['class' => 'bg-green-100 text-green-800', 'text' => 'Entregado'],
            'cancelado' => ['class' => 'bg-red-100 text-red-800', 'text' => 'Cancelado'],
        ];

        return $badges[$this->vent_estado] ?? $badges['borrador'];
    }

    public function getPagoBadgeAttribute()
    {
        $badges = [
            'pendiente' => ['class' => 'bg-red-100 text-red-800', 'text' => 'Pendiente'],
            'parcial' => ['class' => 'bg-yellow-100 text-yellow-800', 'text' => 'Parcial'],
            'completado' => ['class' => 'bg-green-100 text-green-800', 'text' => 'Completado'],
        ];

        return $badges[$this->vent_estado_pago] ?? $badges['pendiente'];
    }

    // Boot method para generar código automático
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->vent_codigo)) {
                $model->vent_codigo = self::generarCodigo($model->vent_tipo);
            }
        });
    }

    public static function generarCodigo($tipo)
    {
        $prefijo = $tipo === 'cotizacion' ? 'COT' : 'VEN';
        $fecha = now()->format('Ymd');
        
        $ultimo = self::where('vent_codigo', 'like', $prefijo . $fecha . '%')
                     ->orderBy('vent_codigo', 'desc')
                     ->first();

        if ($ultimo) {
            $numero = intval(substr($ultimo->vent_codigo, -4)) + 1;
        } else {
            $numero = 1;
        }

        return $prefijo . $fecha . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }
}