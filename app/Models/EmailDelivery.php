<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailDelivery extends Model
{
    protected $fillable = [
        'dedup_key', 'batch_id', 'sender_id', 'recipient_id', 'tenant_id', 'kind', 'status', 'email',
        'name', 'business_name', 'payload', 'attempts', 'sent_at', 'failed_at', 'last_error',
    ];

    protected function casts(): array
    {
        return ['payload' => 'array', 'attempts' => 'integer', 'sent_at' => 'datetime', 'failed_at' => 'datetime'];
    }
}
