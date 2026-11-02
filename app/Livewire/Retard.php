<?php

namespace App\Livewire;

use App\Models\Setting;
use App\Models\Tontine;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class Retard extends Component
{
    use WithPagination;

    public string $search = '';

    public string $minimumDays = '1';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedMinimumDays(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'minimumDays');
        $this->resetPage();
    }

    public function render(): View
    {
        abort_unless(auth()->check(), 403);
        $tenantId = auth()->user()->activeTenantId();
        abort_unless(in_array($this->minimumDays, ['1', '7', '30', 'priority'], true), 422);
        $priorityDays = Setting::current()->priority_days;
        $today = CarbonImmutable::today();
        $tontines = Tontine::query()->where('tenant_id', $tenantId)->where('status', 'active')
            ->whereDate('start_date', '<', $today)
            ->whereHas('customer', fn (Builder $query): Builder => $query->where('tenant_id', $tenantId))
            ->with([
                'customer:id,tenant_id,name,phone',
                'days' => fn (HasMany $query): HasMany => $query->where('tenant_id', $tenantId)->whereDate('due_date', '<', $today),
                'days.payments' => fn (HasMany $query): HasMany => $query->select('id', 'tenant_id', 'tontine_id', 'tontine_day_id', 'voided_at')->where('tenant_id', $tenantId)->whereNull('voided_at'),
            ])->get();

        $arrears = collect();
        foreach ($tontines as $tontine) {
            $start = CarbonImmutable::instance($tontine->start_date);
            $lastDue = CarbonImmutable::instance($tontine->end_date)->min($today->subDay());
            if ($lastDue->lt($start)) {
                continue;
            }
            $expectedDays = Tontine::contributionDaysBetween($start, $lastDue);
            $days = $tontine->days->filter(fn ($day): bool => ! $day->due_date->isSunday() && $day->due_date->betweenIncluded($start, $lastDue));
            $paidDays = $days->filter(fn ($day): bool => $day->payments->contains(fn ($payment): bool => (int) $payment->tontine_id === (int) $tontine->id));
            $missingDays = $expectedDays - $paidDays->count();
            if ($missingDays <= 0) {
                continue;
            }
            $paidDates = $paidDays->mapWithKeys(fn ($day): array => [$day->due_date->toDateString() => true]);
            $firstDue = $start;
            while ($firstDue->isSunday() || $paidDates->has($firstDue->toDateString())) {
                $firstDue = $firstDue->addDay();
            }
            $expectedAmount = $expectedDays * (int) $tontine->daily_amount
                + $days->sum(fn ($day): int => (int) $day->amount - (int) $tontine->daily_amount);
            $arrears->push([
                'tontine' => $tontine,
                'missingDays' => $missingDays,
                'amount' => $expectedAmount - $paidDays->sum('amount'),
                'firstDue' => $firstDue,
                'age' => (int) $firstDue->diffInDays($today),
            ]);
        }

        $totals = [
            'clients' => $arrears->pluck('tontine.customer_id')->unique()->count(),
            'tontines' => $arrears->count(),
            'days' => $arrears->sum('missingDays'),
            'amount' => $arrears->sum('amount'),
        ];
        $search = Str::lower(trim($this->search));
        $filtered = $arrears->filter(function (array $row) use ($search, $priorityDays): bool {
            $tontine = $row['tontine'];

            return $row['missingDays'] >= ($this->minimumDays === 'priority' ? $priorityDays : (int) $this->minimumDays)
                && ($search === '' || Str::contains(Str::lower(implode(' ', [$tontine->reference, $tontine->customer->name, $tontine->customer->phone])), $search));
        })->sortBy(fn (array $row): string => $row['firstDue']->toDateString().'|'.$row['tontine']->reference)->values();
        $page = max(1, (int) $this->getPage());
        $rows = new LengthAwarePaginator($filtered->forPage($page, 15)->values(), $filtered->count(), 15, $page, [
            'path' => request()->url(),
        ]);

        return view('livewire.retard', compact('rows', 'totals', 'priorityDays'))->layout('components.layouts.backend');
    }
}
