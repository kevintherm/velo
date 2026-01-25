<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppConfig extends Model
{
    protected $fillable = ['project_id', 'app_name', 'app_url', 'trusted_proxies', 'rate_limits'];

    protected function casts(): array
    {
        return [
            'trusted_proxies' => 'array',
            'rate_limits' => 'integer', 
        ];
    }
}
