<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetOtp extends Model
{
    protected $fillable = ['email', 'code_hash', 'expires_at', 'attempts'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
