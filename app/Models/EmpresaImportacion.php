<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmpresaImportacion extends Model
{
    use HasFactory;

    protected $table = 'pro_empresas_de_importacion';
    
    protected $primaryKey = 'empresaimp_id';

    protected $fillable = [
        'empresaimp_pais',
        'empresaimp_descripcion',
        'empresaimp_situacion'
    ];

    /**
     * Relación con el país
     */
    public function pais()
    {
        return $this->belongsTo(Pais::class, 'empresaimp_pais', 'pais_id');
    }

    /**
     * Scope para empresas activas
     */
    public function scopeActivos($query)
    {
        return $query->where('empresaimp_situacion', 1);
    }

    /**
     * Scope para empresas con país
     */
    public function scopeConPais($query)
    {
        return $query->with('pais');
    }

    /**
     * Accessor para obtener el estado en texto
     */
    public function getEstadoTextoAttribute()
    {
        return $this->empresaimp_situacion == 1 ? 'Activa' : 'Inactiva';
    }

    /**
     * Accessor para obtener el nombre del país
     */
    public function getNombrePaisAttribute()
    {
        return $this->pais ? $this->pais->pais_descripcion : 'Sin país';
    }
}