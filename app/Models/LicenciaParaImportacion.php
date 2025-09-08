<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LicenciaParaImportacion extends Model
{
    use HasFactory;

    protected $table = 'pro_licencias_para_importacion';
    protected $primaryKey = 'lipaimp_id';

    protected $fillable = [
        'lipaimp_poliza',
        'lipaimp_descripcion',
        'lipaimp_empresa',
        'lipaimp_clase',
        'lipaimp_marca',
        'lipaimp_modelo',
        'lipaimp_calibre',
        'lipaimp_fecha_vencimiento',
        'lipaimp_situacion'
    ];

    protected $casts = [
        'lipaimp_fecha_vencimiento' => 'date',
        'lipaimp_situacion' => 'integer',
        'lipaimp_poliza' => 'integer'
    ];

    // Relaciones
    public function empresa()
    {
        return $this->belongsTo(EmpresaImportacion::class, 'lipaimp_empresa', 'empresaimp_id');
    }

    public function clase()
    {
        return $this->belongsTo(ClasePistola::class, 'lipaimp_clase', 'clase_id');
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'lipaimp_marca', 'marca_id');
    }

    public function modelo()
    {
        return $this->belongsTo(Modelo::class, 'lipaimp_modelo', 'modelo_id');
    }

    public function calibre()
    {
        return $this->belongsTo(Calibre::class, 'lipaimp_calibre', 'calibre_id');
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('lipaimp_situacion', 1);
    }

    public function scopeInactivas($query)
    {
        return $query->where('lipaimp_situacion', 0);
    }

    public function scopeVencidas($query)
    {
        return $query->where('lipaimp_fecha_vencimiento', '<', now())
                    ->where('lipaimp_situacion', 1);
    }

    public function scopePorVencer($query, $dias = 30)
    {
        return $query->whereBetween('lipaimp_fecha_vencimiento', [now(), now()->addDays($dias)])
                    ->where('lipaimp_situacion', 1);
    }

    // Accessors
    public function getEstadoTextAttribute()
    {
        if ($this->lipaimp_situacion == 0) {
            return 'Inactiva';
        }

        if ($this->lipaimp_fecha_vencimiento && $this->lipaimp_fecha_vencimiento->isPast()) {
            return 'Vencida';
        }

        return 'Activa';
    }

    public function getDiasParaVencerAttribute()
    {
        if (!$this->lipaimp_fecha_vencimiento) {
            return null;
        }

        return $this->lipaimp_fecha_vencimiento->diffInDays(now(), false);
    }

    public function getEstaVencidaAttribute()
    {
        return $this->lipaimp_fecha_vencimiento && $this->lipaimp_fecha_vencimiento->isPast();
    }

    public function getEstaPorVencerAttribute()
    {
        if (!$this->lipaimp_fecha_vencimiento) {
            return false;
        }

        $diasParaVencer = $this->dias_para_vencer;
        return $diasParaVencer <= 30 && $diasParaVencer >= 0;
    }
}