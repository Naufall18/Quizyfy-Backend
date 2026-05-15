<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLogs extends Model
{
     protected $fillable = [
        'user_id', 'action', 'model_type', 'model_id', 'data', 'ip_address', 'user_agent',
    ];

    protected $casts = [
        'data' => 'array',
    ];
}
