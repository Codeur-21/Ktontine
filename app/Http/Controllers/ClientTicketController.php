<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Setting;
use App\Models\Tontine;
use App\Models\TontineDay;
use App\Models\TontinePayment;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

class ClientTicketController extends Controller
{
    public function __invoke(Request $request, int $customer): Response
    {
        $tenantId = $request->user()->activeTenantId();
        $customer = Customer::query()->where('tenant_id', $tenantId)->whereNull('deleted_at')->findOrFail($customer);
        $customer->load([
            'tontines' => fn (HasMany $query): HasMany => $query->where('tenant_id', $tenantId)->orderByDesc('start_date')->orderBy('id'),
            'tontines.days' => fn (HasMany $query): HasMany => $query->where('tenant_id', $tenantId)->orderBy('due_date'),
            'tontines.days.payments' => fn (HasMany $query): HasMany => $query->select('id', 'tenant_id', 'tontine_id', 'tontine_day_id', 'amount', 'payment_date', 'voided_at')->where('tenant_id', $tenantId),
        ]);
        $generatedAt = CarbonImmutable::now();
        $today = $generatedAt->startOfDay();
        $latestContribution = $customer->tontines->flatMap(fn (Tontine $tontine): Collection => $tontine->days
            ->filter(fn (TontineDay $day): bool => $day->due_date->betweenIncluded($tontine->start_date, $tontine->end_date)
                && $day->payments->contains(fn (TontinePayment $payment): bool => $payment->voided_at === null
                    && (int) $payment->tontine_id === (int) $tontine->id))
            ->map(fn (TontineDay $day): string => $day->due_date->format('Y-m')))
            ->sortDesc()->first();
        $selectedMonth = $request->query('month', $latestContribution ?? $today->format('Y-m'));
        abort_unless(Validator::make(['month' => $selectedMonth], ['month' => ['required', 'string', 'date_format:Y-m']])->passes(), 422, 'Le mois du ticket est invalide.');
        $monthStart = CarbonImmutable::createFromFormat('!Y-m', $selectedMonth);
        $cycles = collect();
        foreach ($customer->tontines as $tontine) {
            $start = CarbonImmutable::instance($tontine->start_date);
            $end = CarbonImmutable::instance($tontine->end_date);
            $totalDays = max(0, Tontine::contributionDaysBetween($start, $end));
            $days = $tontine->days->filter(fn (TontineDay $day): bool => $day->due_date->betweenIncluded($start, $end));
            $contributionDays = $days->filter(fn (TontineDay $day): bool => ! $day->due_date->isSunday());
            $payments = $days->flatMap(function (TontineDay $day) use ($tontine): Collection {
                return $day->payments->filter(fn (TontinePayment $payment): bool => (int) $payment->tontine_id === (int) $tontine->id)
                    ->map(fn (TontinePayment $payment): array => ['payment' => $payment, 'dueDate' => $day->due_date]);
            })->sortBy(fn (array $row): string => $row['payment']->payment_date->toDateString().'|'.str_pad((string) $row['payment']->id, 20, '0', STR_PAD_LEFT))->values();
            $activePayments = $payments->filter(fn (array $row): bool => $row['payment']->voided_at === null);
            $paidDates = $activePayments->pluck('dueDate')->mapWithKeys(fn (CarbonInterface $date): array => [$date->toDateString() => true]);
            $expected = $totalDays * (int) $tontine->daily_amount
                + $contributionDays->sum(fn (TontineDay $day): int => (int) $day->amount - (int) $tontine->daily_amount);
            $paid = (int) $activePayments->sum('payment.amount');
            $lastDue = $end->min($today->subDay());
            $overdueExpectedDays = $lastDue->lt($start) || $tontine->status !== 'active' ? 0 : Tontine::contributionDaysBetween($start, $lastDue);
            $overdueStored = $contributionDays->filter(fn (TontineDay $day): bool => $day->due_date->lt($today));
            $coveredStored = $overdueStored->filter(fn (TontineDay $day): bool => $paidDates->has($day->due_date->toDateString()));
            $overdueAmount = $overdueExpectedDays === 0 ? 0 : $overdueExpectedDays * (int) $tontine->daily_amount
                + $overdueStored->sum(fn (TontineDay $day): int => (int) $day->amount - (int) $tontine->daily_amount)
                - $coveredStored->sum('amount');
            $cancelledDates = $payments->filter(fn (array $row): bool => $row['payment']->voided_at !== null)
                ->pluck('dueDate')->mapWithKeys(fn (CarbonInterface $date): array => [$date->toDateString() => true]);
            $calendar = collect();
            $calendarStart = $monthStart->startOfWeek();
            $calendarEnd = $monthStart->endOfMonth()->endOfWeek()->startOfDay();
            for ($date = $calendarStart; $date->lte($calendarEnd); $date = $date->addDay()) {
                $dateKey = $date->toDateString();
                $inMonth = $date->isSameMonth($monthStart);
                $state = match (true) {
                    ! $inMonth || ! $date->betweenIncluded($start, $end) => 'outside',
                    $paidDates->has($dateKey) => 'paid',
                    $cancelledDates->has($dateKey) => 'cancelled',
                    $date->isSunday() => 'outside',
                    $tontine->status === 'closed' => 'unpaid',
                    $date->lt($today) => 'overdue',
                    default => 'due',
                };
                $calendar->push(['date' => $dateKey, 'day' => $inMonth ? $date->day : null, 'state' => $state, 'today' => $date->isSameDay($today)]);
            }
            $monthDays = $calendar->filter(fn (array $day): bool => $day['day'] !== null)->keyBy('day');
            $cardCalendar = collect(range(1, 40))->map(fn (int $number): array => $monthDays->get($number, [
                'date' => null, 'day' => $number <= 31 ? $number : null, 'state' => 'unavailable', 'today' => false,
            ]))->chunk(10);
            $storedDays = $days->keyBy(fn (TontineDay $day): string => $day->due_date->toDateString());
            $monthlyExpected = (int) $monthDays->filter(fn (array $day): bool => $day['state'] !== 'outside' && ! CarbonImmutable::parse($day['date'])->isSunday())->sum(
                fn (array $day): int => (int) ($storedDays->get($day['date'])?->amount ?? $tontine->daily_amount),
            );
            $monthlyPaid = (int) $activePayments->filter(fn (array $row): bool => $row['dueDate']->isSameMonth($monthStart))->sum('payment.amount');
            $cycles->push([
                'tontine' => $tontine,
                'totalDays' => $totalDays,
                'paidDays' => $contributionDays->filter(fn (TontineDay $day): bool => $paidDates->has($day->due_date->toDateString()))->count(),
                'expected' => $expected,
                'paid' => $paid,
                'remaining' => max(0, $expected - $paid),
                'overpayment' => max(0, $paid - $expected),
                'overdueDays' => $overdueExpectedDays === 0 ? 0 : $overdueExpectedDays - $coveredStored->count(),
                'overdueAmount' => $overdueAmount,
                'payments' => $payments,
                'calendar' => $calendar->chunk(7),
                'cardCalendar' => $cardCalendar,
                'monthlyExpected' => $monthlyExpected,
                'monthlyPaid' => $monthlyPaid,
                'monthlyRemaining' => max(0, $monthlyExpected - $monthlyPaid),
            ]);
        }
        $totals = [
            'expected' => $cycles->sum('expected'),
            'paid' => $cycles->sum('paid'),
            'remaining' => $cycles->sum('remaining'),
            'overdue' => $cycles->sum('overdueAmount'),
        ];

        return response()->view('tickets.client', [
            'settings' => Setting::current(),
            'customer' => $customer,
            'tenant' => $request->user()->tenant,
            'operator' => $request->user()->name,
            'generatedAt' => $generatedAt,
            'currentMonth' => $monthStart->locale('fr')->translatedFormat('F Y'),
            'selectedMonth' => $selectedMonth,
            'cycles' => $cycles,
            'totals' => $totals,
        ])->withHeaders(['Cache-Control' => 'private, no-store, max-age=0', 'X-Robots-Tag' => 'noindex, nofollow']);
    }
}
