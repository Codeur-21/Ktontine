<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TontinePayment extends Model
{
    use BelongsToTenant;
    use HasFactory;

    /** @return array<string, class-string<Model>> */
    protected function tenantParents(): array
    {
        return [
            'tontine_id' => Tontine::class,
            'tontine_day_id' => TontineDay::class,
            'recorded_by' => User::class,
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (TontinePayment $payment): void {
            abort_unless(
                TontineDay::withoutGlobalScope(TenantScope::class)
                    ->where('tenant_id', $payment->tenant_id)
                    ->where('tontine_id', $payment->tontine_id)
                    ->whereKey($payment->tontine_day_id)
                    ->exists(),
                403,
                'La journée doit appartenir à cette tontine.',
            );
        });
    }

    protected $table = 'tontine_payments';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'voided_at' => 'datetime',
            'history' => 'array',
        ];
    }

    /** @return list<array{action: string, at: ?string, user_id: ?int, user_name: ?string, amount: int, payment_date: string, due_date: string, reason: ?string}> */
    public function auditHistory(): array
    {
        if ($this->history !== null) {
            return $this->history;
        }

        if (! $this->exists) {
            return [];
        }

        $history = [
            [
                'action' => 'recorded',
                'at' => $this->created_at?->toIso8601String(),
                'user_id' => $this->recorded_by,
                'user_name' => $this->recorder?->name,
                'amount' => (int) $this->amount,
                'payment_date' => $this->payment_date->toDateString(),
                'due_date' => $this->day->due_date->toDateString(),
                'reason' => null,
            ],
        ];

        if ($this->voided_at !== null) {
            $history[] = [
                ...$history[0],
                'action' => 'cancelled',
                'at' => $this->voided_at->toIso8601String(),
                'user_id' => null,
                'user_name' => null,
            ];
        }

        return $history;
    }

    public function appendHistory(
        string $action,
        User $actor,
        string $dueDate,
        ?string $reason = null,
    ): void {
        $this->history = [
            ...$this->auditHistory(),
            [
                'action' => $action,
                'at' => now()->toIso8601String(),
                'user_id' => (int) $actor->id,
                'user_name' => $actor->name,
                'amount' => (int) $this->amount,
                'payment_date' => $this->payment_date->toDateString(),
                'due_date' => $dueDate,
                'reason' => $reason,
            ],
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function tontine(): BelongsTo
    {
        return $this->belongsTo(Tontine::class);
    }

    public function day(): BelongsTo
    {
        return $this->belongsTo(TontineDay::class, 'tontine_day_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
