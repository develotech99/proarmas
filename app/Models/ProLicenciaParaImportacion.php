<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProLicenciaParaImportacion extends Model
{
    use HasFactory;

    // Definimos la tabla
    protected $table = 'pro_licencias_para_importacion';

    // Definimos la clave primaria
    protected $primaryKey = 'lipaimp_id';

    // Deshabilitamos el uso de incremento automático para la clave primaria (si usas autoIncrement)
    public $incrementing = true;

    // Indicamos que el tipo de clave primaria es entero
    protected $keyType = 'int';

    // Los campos que pueden ser asignados masivamente
    protected $fillable = [
        'lipaimp_poliza',
        'lipaimp_descripcion',
        'lipaimp_empresa',
        'lipaimp_fecha_vencimiento',
        'lipaimp_situacion'
    ];

    // Relación con la empresa de importación (una licencia pertenece a una empresa)
    public function empresa()
    {
        return $this->belongsTo(ProEmpresaDeImportacion::class, 'lipaimp_empresa', 'empresaimp_id');
    }
}
