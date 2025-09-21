<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersUbicacion extends Model
{
    use HasFactory;

    protected $table = 'users_ubicaciones';
    protected $primaryKey = 'ubi_id';

    protected $fillable =[
        'ubi_user',
        'ubi_latitud',
        'ubi_longitud',
        'ubi_descripcion',
    ];
    
    public function user(){
        return $this->belongsTo(User::class, 'ubi_user', 'user_id');
    }
}
