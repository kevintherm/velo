<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StorageConfig extends Model
{
    protected $fillable = [
        'project_id',
        'provider',
        'endpoint',
        'bucket',
        'region',
        'access_key',
        'secret_key',
        's3_force_path_styling',
    ];

    protected $casts = [
        's3_force_path_styling' => 'boolean',
    ];
}
