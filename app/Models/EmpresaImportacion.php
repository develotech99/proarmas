<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpresaImportacion extends Model
{
    use HasFactory;

    protected $table = 'pro_empresas_de_importacion';
    protected $primaryKey = 'empresaimp_id';

    protected $fillable = [
        'empresaimp_descripcion',
        'empresaimp_pais',
        'empresaimp_situacion'
    ];
    public function scopeActivos($query)
{
    return $query->where('empresaimp_situacion', 1);
}
}
