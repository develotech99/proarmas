<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FelToken extends Model
{
    protected $table = 'fel_tokens';

    protected $fillable = [
        'token', 'token_type', 'expires_in', 'issued_at', 'expires_at', 'is_active'
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];
}
