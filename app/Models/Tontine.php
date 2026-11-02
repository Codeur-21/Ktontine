<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tontine extends Model
{
    use BelongsToTenant;
    use HasFactory;

    /** @return array<string, class-string<Model>> */
    protected function tenantParents(): array
    {
        return ['customer_id' => Customer::class];
    }

    protected $table = 'tontines';

    protected $guarded = ['id'];

    protected static function booted(): void
    {
        static::saving(function (Tontine $tontine): void {
            $tontine->commission_amount = $tontine->daily_amount;
        });
    }

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'closed_at' => 'datetime',
        ];
    }

    public static function contributionEndDate(string $startDate): CarbonImmutable
    {
        $date = CarbonImmutable::parse($startDate);
        $remaining = 30;
        while (true) {
            if (! $date->isSunday()) {
                $remaining--;
            }
            if ($remaining === 0) {
                return $date;
            }
            $date = $date->addDay();
        }
    }

    public static function contributionDaysBetween(CarbonInterface $start, CarbonInterface $end): int
    {
        $count = 0;
        for ($date = CarbonImmutable::instance($start)->startOfDay(); $date->lte($end); $date = $date->addDay()) {
            if (! $date->isSunday()) {
                $count++;
            }
        }

        return $count;
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(TontinePayment::class);
    }

    public function days(): HasMany
    {
        return $this->hasMany(TontineDay::class);
    }
}
