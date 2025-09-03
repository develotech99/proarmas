<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpresaImportacion extends Model
{
    use HasFactory;

    protected $table = 'pro_empresas_de_importacion';
    protected $primaryKey = 'empresaimp_id';
    public $timestamps = true;

    protected $fillable = [
        'empresaimp_pais',
        'empresaimp_descripcion',
        'empresaimp_situacion',
    ];

    public function scopeActivos($query)
{
    return $query->where('empresaimp_situacion', 1);
}
}
