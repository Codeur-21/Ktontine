<?php

namespace App;

use App\Models\Customer;
use App\Models\Tontine;
use App\Models\TontineDay;
use App\Models\TontinePayment;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

class ActivityReport
{
    /** @return array<string, list<string>> */
    public static function rules(): array
    {
        return [
            'startDate' => ['required', 'date_format:Y-m-d'],
            'endDate' => ['required', 'date_format:Y-m-d', 'after_or_equal:startDate', 'before_or_equal:today'],
        ];
    }

    /** @return array<string, string> */
    public static function messages(): array
    {
        return [
            'startDate.required' => 'Indiquez la date de début.',
            'startDate.date_format' => 'La date de début est invalide.',
            'endDate.required' => 'Indiquez la date de fin.',
            'endDate.date_format' => 'La date de fin est invalide.',
            'endDate.after_or_equal' => 'La date de fin doit suivre la date de début.',
            'endDate.before_or_equal' => 'La date de fin ne peut pas être dans le futur.',
        ];
    }

    /** @return array{summary: array<string, int>, clients: Collection, daily: Collection, generatedAt: CarbonImmutable} */
    public function build(string $startDate, string $endDate): array
    {
        abort_unless(auth()->check(), 403);
        $tenantId = auth()->user()->activeTenantId();
        Validator::make(compact('startDate', 'endDate'), self::rules(), self::messages())->validate();
        $start = CarbonImmutable::parse($startDate)->startOfDay();
        $end = CarbonImmutable::parse($endDate)->endOfDay();
        $today = CarbonImmutable::today();
        $payments = TontinePayment::query()->select('id', 'tenant_id', 'tontine_id', 'tontine_day_id', 'amount', 'payment_date', 'voided_at')
            ->where('tenant_id', $tenantId)->whereBetween('payment_date', [$startDate, $endDate])
            ->whereHas('tontine', fn (Builder $query): Builder => $query->where('tenant_id', $tenantId)->whereHas('customer', fn (Builder $client): Builder => $client->where('tenant_id', $tenantId)))
            ->whereHas('day', fn (Builder $query): Builder => $query->where('tenant_id', $tenantId)->whereColumn('tontine_days.tontine_id', 'tontine_payments.tontine_id'))
            ->with('tontine.customer')->orderBy('payment_date')->orderBy('id')->get();
        $active = $payments->whereNull('voided_at');
        $cancelled = $payments->whereNotNull('voided_at');
        $clients = collect();
        foreach ($payments as $payment) {
            $row = $clients->get($payment->tontine->customer_id, $this->emptyClient($payment->tontine->customer));
            $row[$payment->voided_at === null ? 'payments' : 'cancelledPayments']++;
            $row[$payment->voided_at === null ? 'paid' : 'cancelled'] += (int) $payment->amount;
            $clients->put($payment->tontine->customer_id, $row);
        }

        $closed = Tontine::query()->where('tenant_id', $tenantId)->where('status', 'closed')->whereBetween('closed_at', [$start, $end])
            ->whereHas('customer', fn (Builder $query): Builder => $query->where('tenant_id', $tenantId))->with('customer')->get();
        foreach ($closed as $tontine) {
            $row = $clients->get($tontine->customer_id, $this->emptyClient($tontine->customer));
            $row['closedCycles']++;
            $row['plannedCommission'] += (int) $tontine->commission_amount;
            $clients->put($tontine->customer_id, $row);
        }

        $cycles = Tontine::query()->where('tenant_id', $tenantId)->where('status', 'active')
            ->whereDate('start_date', '<=', $endDate)->whereDate('start_date', '<', $today)->whereDate('end_date', '>=', $startDate)
            ->whereHas('customer', fn (Builder $query): Builder => $query->where('tenant_id', $tenantId))
            ->with([
                'customer',
                'days' => fn (HasMany $query): HasMany => $query->where('tenant_id', $tenantId)->whereBetween('due_date', [$startDate, $endDate])->whereDate('due_date', '<', $today),
                'days.payments' => fn (HasMany $query): HasMany => $query->select('id', 'tenant_id', 'tontine_id', 'tontine_day_id')->where('tenant_id', $tenantId)->whereNull('voided_at'),
            ])->get();
        foreach ($cycles as $tontine) {
            $first = CarbonImmutable::instance($tontine->start_date)->max($start);
            $last = CarbonImmutable::instance($tontine->end_date)->min($end->startOfDay())->min($today->subDay());
            if ($last->lt($first)) {
                continue;
            }
            $expectedDays = Tontine::contributionDaysBetween($first, $last);
            $days = $tontine->days->filter(fn (TontineDay $day): bool => ! $day->due_date->isSunday() && $day->due_date->betweenIncluded($first, $last));
            $covered = $days->filter(fn (TontineDay $day): bool => $day->payments->contains(fn (TontinePayment $payment): bool => (int) $payment->tontine_id === (int) $tontine->id));
            $missing = $expectedDays - $covered->count();
            if ($missing === 0) {
                continue;
            }
            $amount = $expectedDays * (int) $tontine->daily_amount
                + $days->sum(fn (TontineDay $day): int => (int) $day->amount - (int) $tontine->daily_amount) - $covered->sum('amount');
            $row = $clients->get($tontine->customer_id, $this->emptyClient($tontine->customer));
            $row['overdueDays'] += $missing;
            $row['overdueAmount'] += $amount;
            $clients->put($tontine->customer_id, $row);
        }

        $daily = $payments->groupBy(fn (TontinePayment $payment): string => $payment->payment_date->toDateString())
            ->map(fn (Collection $items, string $date): array => [
                'date' => $date,
                'payments' => $items->whereNull('voided_at')->count(),
                'paid' => (int) $items->whereNull('voided_at')->sum('amount'),
                'cancelled' => (int) $items->whereNotNull('voided_at')->sum('amount'),
            ])->values();

        return [
            'summary' => [
                'paid' => (int) $active->sum('amount'),
                'payments' => $active->count(),
                'cancelled' => (int) $cancelled->sum('amount'),
                'cancelledPayments' => $cancelled->count(),
                'closedCycles' => $closed->count(),
                'plannedCommission' => (int) $closed->sum('commission_amount'),
                'clients' => $clients->count(),
                'overdueDays' => (int) $clients->sum('overdueDays'),
                'overdueAmount' => (int) $clients->sum('overdueAmount'),
            ],
            'clients' => $clients->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values(),
            'daily' => $daily,
            'generatedAt' => CarbonImmutable::now(),
        ];
    }

    /** @return array{id: int, name: string, reference: string, phone: ?string, payments: int, paid: int, cancelledPayments: int, cancelled: int, closedCycles: int, plannedCommission: int, overdueDays: int, overdueAmount: int} */
    private function emptyClient(Customer $client): array
    {
        return ['id' => (int) $client->id, 'name' => $client->name, 'reference' => $client->reference, 'phone' => $client->phone,
            'payments' => 0, 'paid' => 0, 'cancelledPayments' => 0, 'cancelled' => 0, 'closedCycles' => 0, 'plannedCommission' => 0, 'overdueDays' => 0, 'overdueAmount' => 0];
    }
}
