<?php

namespace App\Models;  // Asegúrate de que el namespace sea el correcto

use Illuminate\Database\Eloquent\Model; // Importa la clase Model

class ProVenta extends Model
{
    protected $table = 'pro_ventas';
    protected $primaryKey = 'ven_id';
    public $timestamps = false;

    protected $fillable = [
        'ven_user',
        'ven_fecha',
        'ven_cliente',
        'ven_total_vendido',
        'ven_descuento',
        'ven_situacion',
        'ven_observaciones'
    ];

    protected $casts = [
        'ven_fecha' => 'date',
        'ven_total_vendido' => 'decimal:2',
        'ven_descuento' => 'decimal:2',
    ];

    // Relación con el vendedor
    public function vendedor()
    {
        return $this->belongsTo(User::class, 'ven_user', 'user_id');
    }

    // Relación con comisiones
    public function comisiones()
    {
        return $this->hasMany(ProPorcentajeVendedor::class, 'porc_vend_ven_id', 'ven_id');
    }

    
}
