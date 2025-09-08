<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LicenciaImportacion extends Model
{
    use HasFactory;

    protected $table = 'pro_licencias_para_importacion';
    protected $primaryKey = 'lipaimp_id';

    protected $casts = [
        'lipaimp_fecha_vencimiento' => 'date',
    ];

    // Relación con empresa
    public function empresa()
    {
        return $this->belongsTo(\App\Models\EmpresaImportacion::class, 'lipaimp_empresa', 'empresaimp_id');
    }

    // Scopes para filtrar licencias
    public function scopePorVencer($query, $dias = 30)
    {
        return $query->where('lipaimp_situacion', 1)
            ->whereDate('lipaimp_fecha_vencimiento', '<=', now()->addDays($dias))
            ->whereDate('lipaimp_fecha_vencimiento', '>', now());
    }

    public function scopeVencidas($query)
    {
        return $query->where('lipaimp_situacion', 1)
            ->whereDate('lipaimp_fecha_vencimiento', '<', now());
    }

    // Atributo calculado para días hasta vencimiento
// Atributo calculado para días hasta vencimiento
public function getDiasHastaVencimientoAttribute()
{
    if (!$this->lipaimp_fecha_vencimiento) {
        return null;
    }
    
    // Para licencias por vencer: devuelve días positivos
    // Para licencias vencidas: devuelve null (ya que usa otro cálculo)
    return now()->diffInDays($this->lipaimp_fecha_vencimiento, false);
}
}