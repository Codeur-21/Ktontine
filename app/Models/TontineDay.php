<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TontineDay extends Model
{
    use BelongsToTenant;
    use HasFactory;

    /** @return array<string, class-string<Model>> */
    protected function tenantParents(): array
    {
        return ['tontine_id' => Tontine::class];
    }

    protected $table = 'tontine_days';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['due_date' => 'date'];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function tontine(): BelongsTo
    {
        return $this->belongsTo(Tontine::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(TontinePayment::class, 'tontine_day_id');
    }
}
