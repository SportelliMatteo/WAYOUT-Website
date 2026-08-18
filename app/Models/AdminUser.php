<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AdminUser extends Model
{
    use HasUuids;

    protected $attributes = [
        'auth_version' => 1,
        'is_active' => true,
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'totp_secret',
        'recovery_codes',
        'last_totp_step',
        'totp_confirmed_at',
        'last_login_at',
        'auth_version',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'totp_secret',
        'recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'totp_secret' => 'encrypted',
            'recovery_codes' => 'array',
            'last_totp_step' => 'integer',
            'totp_confirmed_at' => 'datetime',
            'last_login_at' => 'datetime',
            'auth_version' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
