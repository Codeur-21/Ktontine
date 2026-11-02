<?php

namespace App\Models\Scopes;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Context;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $tenantId = self::tenantId();
        if ($tenantId === null) {
            $builder->whereRaw('1 = 0');

            return;
        }

        $column = method_exists($model, 'tenantColumn') ? $model->tenantColumn() : 'tenant_id';
        $builder->where($model->qualifyColumn($column), $tenantId);
    }

    public static function tenantId(): ?int
    {
        $user = auth()->user();
        $contextId = Context::getHidden('tenant_id');
        if ($user !== null && (! $user->is_active || ($user->tenant_id === null && ! $user->isPlatformAdmin()))) {
            return null;
        }
        if ($user?->tenant_id !== null && $contextId !== null && (int) $user->tenant_id !== (int) $contextId) {
            return null;
        }
        $tenantId = $user?->tenant_id ?? $contextId;
        if ($tenantId === null || ! Tenant::query()->whereKey($tenantId)->where('is_active', true)->exists()) {
            return null;
        }

        return (int) $tenantId;
    }
}
