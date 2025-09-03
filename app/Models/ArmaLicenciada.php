<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArmaLicenciada extends Model
{
    use HasFactory;

    protected $table = 'pro_armas_licenciadas';
    protected $primaryKey = 'arma_id';
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'arma_licencia_id',
        'arma_clase_id',
        'arma_marca_id',
        'arma_modelo_id',
        'arma_calibre_id',
        'arma_cantidad',
        'arma_situacion',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'arma_licencia_id' => 'integer',
        'arma_clase_id' => 'integer',
        'arma_marca_id' => 'integer',
        'arma_modelo_id' => 'integer',
        'arma_calibre_id' => 'integer',
        'arma_cantidad' => 'integer',
        'arma_situacion' => 'integer',
    ];

    /**
     * Scope para obtener solo armas activas
     */
    public function scopeActivos($query)
    {
        return $query->where('arma_situacion', 1);
    }

    /**
     * Accessor para obtener el estado como texto
     */
    public function getEstadoTextoAttribute()
    {
        return $this->arma_situacion == 1 ? 'Activa' : 'Inactiva';
    }

    /**
     * Accessor para descripción completa del arma
     */
    public function getDescripcionCompletaAttribute()
    {
        $clase = $this->clase ? $this->clase->clase_descripcion : 'N/A';
        $marca = $this->marca ? $this->marca->marca_descripcion : 'N/A';
        $modelo = $this->modelo ? $this->modelo->modelo_descripcion : 'N/A';
        $calibre = $this->calibre ? $this->calibre->calibre_nombre : 'N/A';
        
        return "{$clase} {$marca} {$modelo} - {$calibre}";
    }

    /**
     * Relación con licencia
     */
    public function licencia()
    {
        return $this->belongsTo(LicenciaImportacion::class, 'arma_licencia_id', 'lipaimp_id');
    }

    /**
     * Relación con clase de pistola
     */
    public function clase()
    {
        return $this->belongsTo(ClasePistola::class, 'arma_clase_id', 'clase_id');
    }

    /**
     * Relación con marca
     */
    public function marca()
    {
        return $this->belongsTo(Marca::class, 'arma_marca_id', 'marca_id');
    }

    /**
     * Relación con modelo
     */
    public function modelo()
    {
        return $this->belongsTo(Modelo::class, 'arma_modelo_id', 'modelo_id');
    }

    /**
     * Relación con calibre
     */
    public function calibre()
    {
        return $this->belongsTo(Calibre::class, 'arma_calibre_id', 'calibre_id');
    }
}