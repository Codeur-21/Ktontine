<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'status',
        'amount',
        'currency',
        'trial_ends_at',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'trial_ends_at' => 'datetime',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    // Un abonnement appartient à un Tenant
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
