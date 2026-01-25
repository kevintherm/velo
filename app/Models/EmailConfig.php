<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailConfig extends Model
{
    protected $fillable = ['project_id', 'mailer', 'host', 'port', 'username', 'password', 'encryption', 'from_address', 'from_name'];
}
