<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id', 'action', 'method', 'path', 'route_name',
        'ip_address', 'user_agent', 'payload_summary', 'response_status',
    ];

    protected $casts = [
        'payload_summary' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
