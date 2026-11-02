<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Context;

class Tenant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    public function run(callable $callback): mixed
    {
        $user = auth()->user();
        abort_unless($this->exists && $this->newQuery()->whereKey($this->getKey())->where('is_active', true)->exists(), 403);
        abort_if($user !== null && (! $user->is_active || ($user->tenant_id === null ? ! $user->isPlatformAdmin() : (int) $user->tenant_id !== (int) $this->getKey())), 403);

        return Context::scope($callback, hidden: ['tenant_id' => (int) $this->getKey()]);
    }

    // Un Tenant possède plusieurs utilisateurs
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // Un Tenant possède plusieurs abonnements
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function owner(): HasOne
    {
        return $this->hasOne(User::class)->where('role', 'owner');
    }

    public function members(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function tontines(): HasMany
    {
        return $this->hasMany(Tontine::class);
    }

    public function tontineDays(): HasMany
    {
        return $this->hasMany(TontineDay::class);
    }

    public function tontinePayments(): HasMany
    {
        return $this->hasMany(TontinePayment::class);
    }
}
