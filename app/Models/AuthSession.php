<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthSession extends Model
{
    protected $fillable = ['project_id', 'collection_id', 'record_id', 'token_hash', 'expires_at', 'last_used_at', 'device_name', 'ip_address'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'last_used_at' => 'datetime',
        ];
    }

    public static function generateToken(): array
    {
        $plainToken = \Str::random(64);
        $hashed = hash('sha256', $plainToken);

        return [$plainToken, $hashed];
    }
}
