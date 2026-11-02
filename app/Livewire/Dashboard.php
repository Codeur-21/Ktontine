<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Tontine;
use App\Models\TontinePayment;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    public string $commissionMonth = '';

    #[Locked]
    public string $commissionPeriod = '';

    public function mount(): void
    {
        $this->currentCommissionMonth();
    }

    public function currentCommissionMonth(): void
    {
        $this->commissionMonth = now()->format('Y-m');
        $this->updatedCommissionMonth();
    }

    public function updatedCommissionMonth(): void
    {
        $this->validate(['commissionMonth' => ['required', 'date_format:Y-m']], [
            'commissionMonth.required' => 'Choisissez un mois pour consulter votre commission.',
            'commissionMonth.date_format' => 'Choisissez un mois valide.',
        ]);
        $this->commissionPeriod = $this->commissionMonth;
        $this->resetPage('commissionPage');
    }

    private function tontines(int $tenantId): Builder
    {
        return Tontine::query()->where('tenant_id', $tenantId)
            ->whereHas('customer', fn (Builder $query): Builder => $query->where('tenant_id', $tenantId));
    }

    public function render(): View
    {
        abort_unless(auth()->check(), 403);
        $tenantId = auth()->user()->activeTenantId();
        $today = CarbonImmutable::today();
        $weekStart = $today->startOfWeek();
        $weekEnd = $weekStart->endOfWeek();
        $payments = TontinePayment::query()->where('tenant_id', $tenantId)
            ->whereHas('tontine', fn (Builder $query): Builder => $query->where('tenant_id', $tenantId)
                ->whereHas('customer', fn (Builder $customer): Builder => $customer->where('tenant_id', $tenantId)))
            ->whereHas('day', fn (Builder $query): Builder => $query->where('tenant_id', $tenantId)
                ->whereColumn('tontine_days.tontine_id', 'tontine_payments.tontine_id'));
        $dailyTotal = (int) (clone $payments)->whereNull('voided_at')->whereDate('payment_date', $today)->sum('amount');
        $weekly = (clone $payments)->whereNull('voided_at')->whereBetween('payment_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->selectRaw('payment_date, SUM(amount) as total')->groupBy('payment_date')->get()
            ->mapWithKeys(fn ($row): array => [$row->payment_date->toDateString() => (int) $row->total]);
        $weeklyTotal = (int) $weekly->sum();
        $weekDays = collect();
        for ($day = $weekStart; $day->lte($weekEnd); $day = $day->addDay()) {
            $amount = $weekly->get($day->toDateString(), 0);
            $weekDays->push(['date' => $day->toDateString(), 'label' => $day->locale('fr')->translatedFormat('D'), 'amount' => $amount, 'shortAmount' => number_format($amount, 0, ',', ' ')]);
        }
        $chartMaximum = max(1, $weekDays->max('amount'));
        $chartDescription = 'Cotisations de la semaine : '.$weekDays->map(fn (array $day): string => $day['label'].' '.$day['shortAmount'].' GNF')->implode(', ');
        $nberClient = Customer::query()->where('tenant_id', $tenantId)->where('status', 'Actif')->count();
        $active = $this->tontines($tenantId)->where('status', 'active');
        $nberTontine = (clone $active)->count();
        $upcomingEnds = (clone $active)->whereBetween('end_date', [$today->toDateString(), $today->addDays(7)->toDateString()])->count();
        $overdue = collect();
        (clone $active)->whereDate('start_date', '<', $today)
            ->withCount(['days as paid_due_days' => fn (Builder $query): Builder => $query
                ->whereRaw('DAYOFWEEK(due_date) <> 1')->whereColumn('due_date', '>=', 'tontines.start_date')->whereColumn('due_date', '<=', 'tontines.end_date')
                ->whereDate('due_date', '<', $today)->whereHas('payments', fn (Builder $payment): Builder => $payment
                ->whereNull('voided_at')->whereColumn('tontine_payments.tontine_id', 'tontine_days.tontine_id'))])
            ->chunkById(100, function ($cycles) use ($today, $overdue): void {
                foreach ($cycles as $cycle) {
                    $start = CarbonImmutable::instance($cycle->start_date);
                    $end = CarbonImmutable::instance($cycle->end_date)->min($today->subDay());
                    $expected = $end->lt($start) ? 0 : Tontine::contributionDaysBetween($start, $end);
                    if ($expected > $cycle->paid_due_days) {
                        $overdue->put($cycle->customer_id, true);
                    }
                }
            });
        $overdueCustomers = $overdue->count();
        $priorityCount = (int) ($overdueCustomers > 0) + (int) ($upcomingEnds > 0);
        $lastCotisations = (clone $payments)->with('tontine.customer')->orderByDesc('created_at')->orderByDesc('id')->paginate(10, pageName: 'paymentsPage');

        $validCycles = $this->tontines($tenantId)->whereIn('status', ['active', 'closed'])
            ->whereColumn('end_date', '>=', 'start_date')->where('daily_amount', '>', 0);
        $portfolio = (clone $validCycles)->where('status', 'active');
        $portfolioCommissionTotal = (int) (clone $portfolio)->sum('daily_amount');
        $portfolioCommissionCustomerCount = (clone $portfolio)->distinct()->count('customer_id');
        $month = CarbonImmutable::createFromFormat('!Y-m', $this->commissionPeriod);
        $commissionMonthLabel = ucfirst($month->locale('fr')->translatedFormat('F Y'));
        $monthly = (clone $validCycles)->whereBetween('end_date', [$month->toDateString(), $month->endOfMonth()->toDateString()]);
        $commissionTotal = (int) (clone $monthly)->sum('daily_amount');
        $commissionCycleCount = (clone $monthly)->count();
        $commissionCustomerCount = (clone $monthly)->distinct()->count('customer_id');
        $commissionCustomers = (clone $monthly)->select('customer_id')
            ->selectRaw('SUM(daily_amount) as monthly_commission, COUNT(*) as cycles_count')
            ->groupBy('customer_id')->with('customer:id,tenant_id,name,reference')
            ->orderByDesc('monthly_commission')->orderBy('customer_id')->paginate(8, pageName: 'commissionPage');

        $historyYear = $month->year;
        $commissionHistory = collect(range(1, 12))->mapWithKeys(function (int $number) use ($historyYear): array {
            $date = CarbonImmutable::create($historyYear, $number, 1);

            return [$number => ['month' => $date->format('Y-m'), 'label' => ucfirst($date->locale('fr')->translatedFormat('F')), 'expected' => 0, 'earned' => 0, 'cycles' => 0, 'settled' => 0]];
        });
        (clone $validCycles)->whereBetween('end_date', [$month->startOfYear()->toDateString(), $month->endOfYear()->toDateString()])
            ->withCount(['days as fully_paid_days' => fn (Builder $query): Builder => $query
                ->where('tenant_id', $tenantId)->whereColumn('amount', '>=', 'tontines.daily_amount')
                ->whereRaw('DAYOFWEEK(due_date) <> 1')->whereColumn('due_date', '>=', 'tontines.start_date')->whereColumn('due_date', '<=', 'tontines.end_date')
                ->whereHas('payments', fn (Builder $payment): Builder => $payment
                    ->where('tenant_id', $tenantId)->whereNull('voided_at')->whereDate('payment_date', '<=', $today)
                    ->whereColumn('tontine_payments.tontine_id', 'tontine_days.tontine_id')
                    ->whereColumn('tontine_payments.amount', '>=', 'tontine_days.amount'))])
            ->chunkById(100, function ($cycles) use ($commissionHistory, $today): void {
                foreach ($cycles as $cycle) {
                    $start = CarbonImmutable::instance($cycle->start_date);
                    $end = CarbonImmutable::instance($cycle->end_date);
                    $row = $commissionHistory->get($end->month);
                    $row['expected'] += (int) $cycle->daily_amount;
                    $row['cycles']++;
                    $requiredDays = Tontine::contributionDaysBetween($start, $end);
                    if ($end->lte($today) && (int) $cycle->fully_paid_days === $requiredDays) {
                        $row['earned'] += (int) $cycle->daily_amount;
                        $row['settled']++;
                    }
                    $commissionHistory->put($end->month, $row);
                }
            });
        $annualEarnedCommission = (int) $commissionHistory->sum('earned');
        $annualExpectedCommission = (int) $commissionHistory->sum('expected');

        return view('livewire.dashboard', compact(
            'dailyTotal', 'weeklyTotal', 'weekDays', 'weekStart', 'weekEnd', 'chartMaximum', 'chartDescription',
            'nberClient', 'nberTontine', 'overdueCustomers', 'upcomingEnds', 'priorityCount', 'lastCotisations',
            'portfolioCommissionTotal', 'portfolioCommissionCustomerCount', 'commissionMonthLabel', 'commissionTotal',
            'commissionCycleCount', 'commissionCustomerCount', 'commissionCustomers',
            'commissionHistory', 'historyYear', 'annualEarnedCommission', 'annualExpectedCommission',
        ))->layout('components.layouts.backend');
    }
}
