<?php

namespace App\Models\Concerns;

use App\Models\Scopes\TenantScope;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);
        static::saving(fn (Model $model) => $model->ensureTenantOwnership());
        static::deleting(fn (Model $model) => $model->ensureTenantOwnership());
    }

    public function tenantColumn(): string
    {
        return 'tenant_id';
    }

    /** @return array<string, class-string<Model>> */
    protected function tenantParents(): array
    {
        return [];
    }

    protected function ensureTenantOwnership(): void
    {
        $column = $this->tenantColumn();
        $tenantId = TenantScope::tenantId();
        if ($tenantId === null && auth()->user()?->isPlatformAdmin()) {
            $tenantId = $this->getAttribute($column);
        }
        abort_unless($tenantId !== null && Tenant::query()->whereKey($tenantId)->exists(), 403, 'Un contexte tenant valide est obligatoire.');
        abort_unless($this->getAttribute($column) === null || (int) $this->getAttribute($column) === (int) $tenantId, 403, 'Cette donnée appartient à un autre tenant.');
        if ($this->exists) {
            abort_unless((int) $this->getRawOriginal($column) === (int) $tenantId, 403, 'Le tenant d’une donnée ne peut pas être changé.');
        }
        $this->setAttribute($column, (int) $tenantId);
        foreach ($this->tenantParents() as $foreignKey => $parentClass) {
            $parentId = $this->getAttribute($foreignKey);
            if ($parentId !== null) {
                abort_unless($parentClass::withoutGlobalScope(TenantScope::class)->where('tenant_id', $tenantId)->whereKey($parentId)->exists(), 403, 'La relation doit appartenir au même tenant.');
            }
        }
    }
}
