<?php

namespace App\Livewire;

use App\Models\Tontine;
use App\Models\TontinePayment;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Calendrier extends Component
{
    public string $month = '';

    public string $search = '';

    public ?int $selectedTontineId = null;

    /** @var list<int> */
    #[Locked]
    public array $defaultTicketIds = [];

    public function mount(): void
    {
        $this->month = now()->format('Y-m');
        $this->defaultTicketIds = $this->tontinesQuery()->inRandomOrder()->limit(3)->pluck('id')->all();
        $this->selectedTontineId = $this->defaultTicketIds[0] ?? null;
    }

    public function previousMonth(): void
    {
        $this->month = $this->monthStart()->subMonthNoOverflow()->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->month = $this->monthStart()->addMonthNoOverflow()->format('Y-m');
    }

    public function goToCurrentMonth(): void
    {
        $this->month = now()->format('Y-m');
    }

    public function selectTontine(int $tontineId): void
    {
        $this->selectedTontineId = $this->tontinesQuery()->findOrFail($tontineId)->id;
    }

    public function render()
    {
        $monthStart = $this->monthStart();
        $monthEnd = $monthStart->endOfMonth();
        $gridStart = $monthStart->startOfWeek();
        $gridEnd = $monthEnd->endOfWeek();

        $search = trim($this->search);

        $tontines = $this->tontinesQuery()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('reference', 'like', '%'.$search.'%')
                        ->orWhereHas('customer', function (Builder $query) use ($search): void {
                            $query
                                ->where('name', 'like', '%'.$search.'%')
                                ->orWhere('phone', 'like', '%'.$search.'%')
                                ->orWhere('reference', 'like', '%'.$search.'%');
                        });
                });
            })
            ->with('customer:id,name,phone')
            ->withCount([
                'payments as paid_days_count' => fn (Builder $query) => $query->where('tenant_id', $this->tenantId())->whereNull('voided_at'),
            ])
            ->withSum([
                'payments as paid_amount' => fn (Builder $query) => $query->where('tenant_id', $this->tenantId())->whereNull('voided_at'),
            ], 'amount')
            ->orderBy('reference')
            ->when($search === '', fn (Builder $query): Builder => $query->whereIn('id', $this->defaultTicketIds))
            ->get();

        $selectedTontine = $tontines->firstWhere('id', $this->selectedTontineId) ?? $tontines->first();

        if ($selectedTontine !== null && $selectedTontine->id !== $this->selectedTontineId) {
            $this->selectedTontineId = $selectedTontine->id;
        }

        $daysByDate = $selectedTontine?->days()
            ->where('tenant_id', $this->tenantId())
            ->whereBetween('due_date', [$gridStart->toDateString(), $gridEnd->toDateString()])
            ->with(['payments' => fn ($query) => $query->where('tenant_id', $this->tenantId())->whereNull('voided_at')])
            ->get()
            ->keyBy(fn ($day) => $day->due_date->toDateString()) ?? collect();

        $calendarDays = collect();
        for ($date = $gridStart; $date->lte($gridEnd); $date = $date->addDay()) {
            $tontineDay = $daysByDate->get($date->toDateString());
            $isDue = ! $date->isSunday() && $selectedTontine !== null
                && $date->betweenIncluded($selectedTontine->start_date, $selectedTontine->end_date);

            $calendarDays->push([
                'date' => $date->toDateString(),
                'day' => $date->day,
                'is_current_month' => $date->isSameMonth($monthStart),
                'is_today' => $date->isToday(),
                'is_due' => $isDue,
                'is_future' => $date->isAfter(CarbonImmutable::today()),
                'is_paid' => $tontineDay?->payments->isNotEmpty() ?? false,
            ]);
        }

        $today = CarbonImmutable::today();
        $todayTontines = $this->tontinesQuery()
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today);

        $paidToday = TontinePayment::query()
            ->where('tenant_id', $this->tenantId())
            ->whereHas('tontine', fn (Builder $query): Builder => $query->where('tenant_id', $this->tenantId())->whereHas('customer', fn (Builder $client): Builder => $client->where('tenant_id', $this->tenantId())))
            ->whereNull('voided_at')
            ->whereDate('payment_date', $today);

        return view('livewire.calendrier', [
            'calendarDays' => $calendarDays,
            'monthLabel' => ucfirst($monthStart->locale('fr')->translatedFormat('F Y')),
            'paidTodayAmount' => (clone $paidToday)->sum('amount'),
            'paidTodayCount' => (clone $paidToday)->count(),
            'selectedTontine' => $selectedTontine,
            'todayDueAmount' => $today->isSunday() ? 0 : (clone $todayTontines)->sum('daily_amount'),
            'todayDueCount' => $today->isSunday() ? 0 : (clone $todayTontines)->count(),
            'tontines' => $tontines,
        ])->layout('components.layouts.backend');
    }

    private function tontinesQuery(): Builder
    {
        return Tontine::query()
            ->where('tenant_id', $this->tenantId())
            ->where('status', 'active')
            ->whereHas('customer', fn (Builder $query): Builder => $query->where('tenant_id', $this->tenantId()));
    }

    private function tenantId(): int
    {
        abort_unless(auth()->check(), 403);

        return auth()->user()->activeTenantId();
    }

    private function monthStart(): CarbonImmutable
    {
        $monthStart = CarbonImmutable::createFromFormat('!Y-m-d', $this->month.'-01');

        abort_unless(
            $monthStart !== false && $monthStart->format('Y-m') === $this->month,
            422,
        );

        return $monthStart;
    }
}
