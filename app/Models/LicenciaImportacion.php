<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LicenciaImportacion extends Model
{
    use HasFactory;

    protected $table = 'pro_licencias_para_importacion';
    protected $primaryKey = 'lipaimp_id';
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'lipaimp_poliza',
        'lipaimp_descripcion',
        'lipaimp_empresa',
        'lipaimp_fecha_vencimiento',
        'lipaimp_situacion',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'lipaimp_poliza' => 'integer',
        'lipaimp_empresa' => 'integer',
        'lipaimp_fecha_vencimiento' => 'date',
        'lipaimp_situacion' => 'integer',
    ];

    /**
     * Scope para obtener solo licencias activas
     */
    public function scopeActivos($query)
    {
        return $query->where('lipaimp_situacion', 1);
    }

    /**
     * Accessor para obtener el estado como texto
     */
    public function getEstadoTextoAttribute()
    {
        return $this->lipaimp_situacion == 1 ? 'Activa' : 'Inactiva';
    }

    /**
     * Accessor para verificar si está vencida
     */
    public function getEstaVencidaAttribute()
    {
        if (!$this->lipaimp_fecha_vencimiento) return false;
        return $this->lipaimp_fecha_vencimiento->isPast();
    }

    /**
     * Accessor para estado con vencimiento
     */
    public function getEstadoCompletoAttribute()
    {
        if ($this->lipaimp_situacion == 0) return 'Inactiva';
        if ($this->esta_vencida) return 'Vencida';
        return 'Activa';
    }

    /**
     * Relación con empresa de importación
     */
    public function empresa()
    {
        return $this->belongsTo(EmpresaImportacion::class, 'lipaimp_empresa', 'empresaimp_id');
    }

    /**
     * Relación con armas licenciadas
     */
    public function armasLicenciadas()
    {
        return $this->hasMany(ArmaLicenciada::class, 'arma_licencia_id', 'lipaimp_id');
    }

    /**
     * Relación con armas activas
     */
    public function armasActivas()
    {
        return $this->hasMany(ArmaLicenciada::class, 'arma_licencia_id', 'lipaimp_id')
                   ->where('arma_situacion', 1);
    }

    /**
     * Accessor para total de armas
     */
    public function getTotalArmasAttribute()
    {
        return $this->armasLicenciadas()->sum('arma_cantidad');
    }

    /**
     * Accessor para total de armas activas
     */
    public function getTotalArmasActivasAttribute()
    {
        return $this->armasActivas()->sum('arma_cantidad');
    }
}