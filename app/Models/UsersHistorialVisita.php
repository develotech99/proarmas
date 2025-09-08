<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersHistorialVisita extends Model
{
    use HasFactory;

    protected $primaryKey = 'hist_id';

    protected $fillable = [
        'hist_visita_id',
        'hist_fecha_actualizacion',
        'hist_estado_anterior',
        'hist_estado_nuevo',
        'hist_total_venta_anterior',
        'hist_total_venta_nuevo',
        'hist_descripcion'
    ];

    public function visita(){
        return $this->belongsTo(UsersVisita::class, 'hist_visita_id', 'visita_id');
    }

}
