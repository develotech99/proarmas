<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProLicenciaParaImportacion extends Model
{
    protected $table = 'pro_licencias_para_importacion';
    protected $primaryKey = 'lipaimp_id';
    public $timestamps = true;

    // Opción A (recomendada): lista explícita de fillables
    protected $fillable = [
        'lipaimp_modelo',
        'lipaimp_largo_canon',
        'lipaimp_poliza',
        'lipaimp_numero_licencia',
        'lipaimp_descripcion',
        'lipaimp_empresa',
        'lipaimp_fecha_emision',
        'lipaimp_fecha_vencimiento',
        'lipaimp_observaciones',
        'lipaimp_situacion',
        'lipaimp_cantidad_armas',
    ];

    // O opción B (desarrollo rápido): permitir todo (quita protección)
    // protected $guarded = [];

    protected $casts = [
        'lipaimp_modelo'            => 'integer',
        'lipaimp_empresa'           => 'integer',
        'lipaimp_largo_canon'       => 'decimal:2',
        'lipaimp_poliza'            => 'integer',
        'lipaimp_cantidad_armas'    => 'integer',
        'lipaimp_situacion'         => 'integer',
        'lipaimp_fecha_emision'     => 'date',
        'lipaimp_fecha_vencimiento' => 'date',
    ];

    // Relaciones que usas en with(['empresa','modelo'])
    public function empresa()
    {
        return $this->belongsTo(ProEmpresaDeImportacion::class, 'lipaimp_empresa', 'empresaimp_id');
    }

    public function modelo()
    {
        return $this->belongsTo(ProModelo::class, 'lipaimp_modelo', 'modelo_id');
    }
}
