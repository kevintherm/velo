<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthPasswordReset extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'collection_id',
        'record_id',
        'email',
        'token',
        'expires_at',
        'used_at',
        'device_name',
        'ip_address',
        'created_at',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function record(): BelongsTo
    {
        return $this->belongsTo(Record::class);
    }

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'created_at' => 'datetime',
    ];
}
