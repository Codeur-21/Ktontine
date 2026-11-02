<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenantThrough;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contribution extends Model
{
    use BelongsToTenantThrough;

    protected function tenantRelationships(): array
    {
        return ['tontine_id' => 'tontine', 'customer_id' => 'customer'];
    }

    public function tontine(): BelongsTo
    {
        return $this->belongsTo(Tontine::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
