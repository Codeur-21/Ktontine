<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Customer extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $fillable = ['name', 'phone', 'address', 'status', 'tenant_id', 'reference'];

    protected $attributes = ['phone' => '', 'address' => '', 'status' => 'Actif'];

    public static function data(): array
    {
        return ['Actif', 'Inactif'];
    }

    protected static function booted(): void
    {
        static::creating(function (Customer $customer): void {
            if (empty($customer->reference)) {
                do {
                    $reference = 'CL-'.Str::upper(Str::random(7));
                } while (static::withoutGlobalScope(TenantScope::class)->withTrashed()->where('reference', $reference)->exists());
                $customer->reference = $reference;
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function tontines(): HasMany
    {
        return $this->hasMany(Tontine::class);
    }
}
