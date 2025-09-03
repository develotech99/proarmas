<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProModelo extends Model
{
    protected $table = 'pro_modelo';

    protected $primaryKey = 'modelo_id';
    protected $fillable = [
        'modelo_descripcion',
        'modelo_situacion',

    ];

    protected $casts = [
        'modelo_situacion' => 'boolean',
    ];

    public function scopeActivos($query)
    {
        return $query->where('modelo_situacion', 1);
    }
}
