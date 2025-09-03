<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marcas extends Model
{
    use HasFactory;

    protected $table = 'pro_marcas';
    
    protected $primaryKey = 'marca_id';

    protected $fillable = [
        'marca_descripcion',
        'marca_situacion'
    ];


    public function scopeActivos($query)
    {
        return $query->where('marca_situacion', 1);
    }
    
}