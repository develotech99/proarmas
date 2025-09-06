<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersVisita extends Model
{
    use HasFactory;

    protected $primaryKey = 'visita_id';

    protected $fillable = [
        'visita_user',
        'visita_fecha',
        'visita_estado',
        'visita_venta',
        'visita_descripcion',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'visita_user', 'user_id');
    }
}
