<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToTenantThrough
{
    protected static function bootBelongsToTenantThrough(): void
    {
        static::addGlobalScope('tenant', function (Builder $query): void {
            foreach ((new static)->tenantRelationships() as $relationship) {
                $query->whereHas($relationship);
            }
        });
        static::saving(fn (Model $model) => $model->ensureParentTenantOwnership());
        static::deleting(fn (Model $model) => $model->ensureParentTenantOwnership());
    }

    /** @return array<string, string> */
    abstract protected function tenantRelationships(): array;

    protected function ensureParentTenantOwnership(): void
    {
        foreach ($this->tenantRelationships() as $foreignKey => $relationship) {
            $relation = $this->{$relationship}();
            abort_unless($relation->exists(), 403, 'La relation doit appartenir au tenant courant.');
            if ($this->exists && $this->isDirty($foreignKey)) {
                abort_unless($relation->getRelated()->newQuery()->whereKey($this->getRawOriginal($foreignKey))->exists(), 403);
            }
        }
    }
}
