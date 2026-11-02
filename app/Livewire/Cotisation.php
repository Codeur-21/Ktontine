<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Tontine;
use App\Models\TontineDay;
use App\Models\TontinePayment;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class Cotisation extends Component
{
    use WithPagination;

    public string $customerId = '';

    public string $tontineId = '';

    /** @var list<string> */
    public array $selectedDates = [];

    #[Locked]
    public bool $customSelection = true;

    public string $periodStart = '';

    public string $periodEnd = '';

    public string $paymentDate = '';

    public string $selectedMonth = '';

    public string $filterMonth = '';

    public string $paymentEndDate = '';

    public string $amount = '';

    public string $receivedDate = '';

    #[Locked]
    public ?int $cancellingId = null;

    public string $cancellationReason = '';

    public string $search = '';

    public string $filterDate = '';

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'filterDate', 'filterMonth'], true)) {
            $this->resetPage();
        }
    }

    public function mount(): void
    {
        $this->paymentDate = now()->toDateString();
        $this->receivedDate = now()->toDateString();
        $tontineId = request()->query('tontine');
        if ($tontineId === null) {
            return;
        }

        abort_unless(is_string($tontineId) && ctype_digit($tontineId), 404);
        $tontine = $this->tontinesQuery()->findOrFail($tontineId);
        $day = request()->query('day', now()->toDateString());
        abort_unless(Validator::make(['paymentDate' => $day], [
            'paymentDate' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
        ])->passes(), 422, 'La journée cotisée est invalide ou située dans le futur.');
        abort_unless($day >= $tontine->start_date->toDateString() && $day <= $tontine->end_date->toDateString(), 422);
        abort_if(CarbonImmutable::parse($day)->isSunday(), 422, 'Les dimanches sont exclus des cotisations.');
        $this->customerId = (string) $tontine->customer_id;
        $this->tontineId = (string) $tontine->id;
        $this->selectedDates = [$day];
        $this->paymentDate = $day;
        $this->selectedMonth = substr($day, 0, 7);
        $this->filterMonth = $this->selectedMonth;
        $this->updateAmount();
    }

    public function updatedCustomerId(): void
    {
        $this->reset('tontineId', 'amount', 'paymentEndDate', 'selectedMonth', 'selectedDates', 'customSelection', 'periodStart', 'periodEnd');
        $this->resetValidation();
    }

    public function updatedTontineId(): void
    {
        $this->reset('paymentEndDate', 'selectedMonth', 'selectedDates', 'customSelection', 'periodStart', 'periodEnd');
        $this->resetValidation();
        $tontine = $this->selectedTontine();
        if ($tontine !== null) {
            $today = CarbonImmutable::today();
            $date = $today->lt($tontine->start_date) ? $tontine->start_date : ($today->gt($tontine->end_date) ? $tontine->end_date : $today);
            $this->selectedMonth = $date->format('Y-m');
        }
        $this->updateAmount();
    }

    public function updatedSelectedMonth(): void
    {
        $this->validateOnly('selectedMonth', ['selectedMonth' => ['required', 'date_format:Y-m']], [
            'selectedMonth.required' => 'Choisissez un mois.', 'selectedMonth.date_format' => 'Le mois est invalide.',
        ]);
    }

    private function selectedTontine(): ?Tontine
    {
        return $this->tontinesQuery()->where('customer_id', $this->customerId)->find($this->tontineId);
    }

    /** @param list<string> $dates */
    private function setSelectedDates(array $dates): void
    {
        $dates = array_values(array_unique($dates));
        sort($dates);
        if (count($dates) > 366) {
            throw ValidationException::withMessages(['selectedDates' => 'Sélectionnez au maximum 366 journées par paiement.']);
        }
        $this->customSelection = true;
        $this->selectedDates = $dates;
        $this->resetValidation(['selectedDates', 'amount', 'paymentDate']);
        $this->updateAmount();
    }

    public function updatedSelectedDates(): void
    {
        $this->customSelection = true;
        $this->updateAmount();
    }

    public function toggleDay(string $date): void
    {
        if (in_array($date, $this->selectedDates, true)) {
            $this->setSelectedDates(array_values(array_diff($this->selectedDates, [$date])));

            return;
        }
        $tontine = $this->selectedTontine();
        abort_if($tontine === null, 404);
        $valid = Validator::make(['date' => $date], ['date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today']])->passes();
        if (! $valid || CarbonImmutable::parse($date)->isSunday() || $date < $tontine->start_date->toDateString() || $date > $tontine->end_date->toDateString()) {
            throw ValidationException::withMessages(['selectedDates' => 'Choisissez une journée passée ou actuelle, dans la période de la tontine.']);
        }
        if ($this->paymentsQuery()->where('tontine_id', $tontine->id)->whereNull('voided_at')->whereHas('day', fn (Builder $query): Builder => $query->where('due_date', $date))->exists()) {
            throw ValidationException::withMessages(['selectedDates' => 'Cette journée est déjà payée.']);
        }
        $this->setSelectedDates([...$this->selectedDates, $date]);
    }

    public function clearSelection(): void
    {
        $this->setSelectedDates([]);
    }

    public function removePeriod(string $start, string $end): void
    {
        $this->setSelectedDates(array_values(array_filter($this->selectedDates, fn (string $date): bool => $date < $start || $date > $end)));
    }

    public function changeMonth(int $direction): void
    {
        abort_unless(in_array($direction, [-1, 1], true), 422);
        $this->updatedSelectedMonth();
        $this->selectedMonth = CarbonImmutable::createFromFormat('!Y-m', $this->selectedMonth)->addMonths($direction)->format('Y-m');
    }

    public function selectMonth(): void
    {
        $this->updatedSelectedMonth();
        $tontine = $this->selectedTontine();
        abort_if($tontine === null, 404);
        $dates = collect($this->calendarDays($tontine))->filter(fn (array $day): bool => $day['eligible'])->pluck('date')->all();
        if ($dates === []) {
            $this->addError('selectedDates', 'Aucune journée impayée à sélectionner pour ce mois.');

            return;
        }
        $this->setSelectedDates([...$this->selectedDates, ...$dates]);
    }

    public function addPeriod(): void
    {
        $this->validate([
            'periodStart' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'periodEnd' => ['required', 'date_format:Y-m-d', 'after_or_equal:periodStart', 'before_or_equal:today'],
        ], [
            'periodStart.required' => 'Indiquez le début de la période.',
            'periodEnd.required' => 'Indiquez la fin de la période.',
            'periodStart.date_format' => 'La date de début est invalide.',
            'periodEnd.date_format' => 'La date de fin est invalide.',
            'periodEnd.after_or_equal' => 'La fin doit suivre le début de la période.',
            'periodStart.before_or_equal' => 'La période ne peut pas être dans le futur.',
            'periodEnd.before_or_equal' => 'La période ne peut pas être dans le futur.',
        ]);
        $tontine = $this->selectedTontine();
        abort_if($tontine === null, 404);
        $start = CarbonImmutable::parse($this->periodStart);
        $end = CarbonImmutable::parse($this->periodEnd);
        if ($start->lt($tontine->start_date) || $end->gt($tontine->end_date) || $start->diffInDays($end) > 365) {
            throw ValidationException::withMessages(['periodEnd' => 'Choisissez au maximum 366 jours, dans la période de la tontine.']);
        }
        $paidDates = $this->paymentsQuery()->where('tontine_id', $tontine->id)->whereNull('voided_at')
            ->whereHas('day', fn (Builder $query): Builder => $query->whereBetween('due_date', [$this->periodStart, $this->periodEnd]))
            ->with('day')->get()->mapWithKeys(fn (TontinePayment $payment): array => [$payment->day->due_date->toDateString() => true]);
        $dates = [];
        for ($date = $start; $date->lte($end); $date = $date->addDay()) {
            if (! $date->isSunday() && ! $paidDates->has($date->toDateString())) {
                $dates[] = $date->toDateString();
            }
        }
        if ($dates === []) {
            $this->addError('periodEnd', 'Toutes les journées de cette période sont déjà payées.');

            return;
        }
        $this->setSelectedDates([...$this->selectedDates, ...$dates]);
        $this->reset('periodStart', 'periodEnd');
    }

    /** @return list<array{date: string, day: ?int, eligible: bool, paid: bool, overdue: bool, selected: bool, today: bool}> */
    private function calendarDays(Tontine $tontine): array
    {
        if (Validator::make(['month' => $this->selectedMonth], ['month' => ['required', 'date_format:Y-m']])->fails()) {
            return [];
        }
        $month = CarbonImmutable::createFromFormat('!Y-m', $this->selectedMonth);
        $today = CarbonImmutable::today();
        $stored = $tontine->days()->whereBetween('due_date', [$month->toDateString(), $month->endOfMonth()->toDateString()])
            ->with(['payments' => fn (HasMany $query): HasMany => $query->whereNull('voided_at')->where('tontine_id', $tontine->id)])->get()
            ->keyBy(fn (TontineDay $day): string => $day->due_date->toDateString());
        $days = [];
        for ($date = $month->startOfWeek(); $date->lte($month->endOfMonth()->endOfWeek()); $date = $date->addDay()) {
            $key = $date->toDateString();
            $inMonth = $date->isSameMonth($month);
            $paid = $stored->get($key)?->payments->isNotEmpty() ?? false;
            $eligible = ! $date->isSunday() && $inMonth && ! $paid && $date->lte($today) && $date->betweenIncluded($tontine->start_date, $tontine->end_date);
            $days[] = ['date' => $key, 'day' => $inMonth ? $date->day : null, 'eligible' => $eligible, 'paid' => $inMonth && $paid, 'overdue' => $eligible && $date->lt($today), 'selected' => in_array($key, $this->selectedDates, true), 'today' => $date->isSameDay($today)];
        }

        return $days;
    }

    /** @param list<string> $dates
     * @return list<array{start: string, end: string, count: int}>
     */
    private function selectedPeriods(array $dates): array
    {
        $periods = [];
        foreach ($dates as $date) {
            $index = count($periods) - 1;
            if ($index >= 0 && CarbonImmutable::parse($periods[$index]['end'])->addDay()->toDateString() === $date) {
                $periods[$index]['end'] = $date;
                $periods[$index]['count']++;
            } else {
                $periods[] = ['start' => $date, 'end' => $date, 'count' => 1];
            }
        }

        return $periods;
    }

    public function updatedPaymentDate(): void
    {
        $this->customSelection = false;
        $this->selectedDates = [];
        $this->reset('paymentEndDate');
        if (Validator::make(['date' => $this->paymentDate], ['date' => ['required', 'date_format:Y-m-d']])->passes()) {
            $this->selectedMonth = substr($this->paymentDate, 0, 7);
        }
        $this->updateAmount();
    }

    public function updatedPaymentEndDate(): void
    {
        $this->customSelection = false;
        $this->selectedDates = [];
        $this->updateAmount();
    }

    /** @return list<string> */
    private function paymentDates(): array
    {
        if ($this->customSelection) {
            if (Validator::make(['dates' => $this->selectedDates], ['dates' => ['array', 'max:366'], 'dates.*' => ['required', 'string', 'date_format:Y-m-d', 'distinct']])->fails()) {
                return [];
            }
            $dates = array_values($this->selectedDates);
            sort($dates);

            return $dates;
        }
        $end = $this->paymentEndDate ?: $this->paymentDate;
        if (Validator::make(['start' => $this->paymentDate, 'end' => $end], [
            'start' => ['required', 'date_format:Y-m-d'],
            'end' => ['required', 'date_format:Y-m-d', 'after_or_equal:start'],
        ])->fails()) {
            return [];
        }
        $start = CarbonImmutable::parse($this->paymentDate);
        $last = CarbonImmutable::parse($end);
        if ($start->diffInDays($last) > 365) {
            return [];
        }
        $dates = [];
        for ($date = $start; $date->lte($last); $date = $date->addDay()) {
            if (! $date->isSunday()) {
                $dates[] = $date->toDateString();
            }
        }

        return $dates;
    }

    private function updateAmount(): void
    {
        $tontine = $this->tontinesQuery()->where('customer_id', $this->customerId)->find($this->tontineId);
        if ($tontine === null) {
            $this->amount = '';

            return;
        }
        $dates = $this->paymentDates();
        $storedAmounts = $tontine->days()->where('tenant_id', $this->tenantId())->whereIn('due_date', $dates)->pluck('amount', 'due_date');
        $this->amount = (string) ($dates === [] ? ($this->customSelection ? 0 : $tontine->daily_amount) : collect($dates)->sum(fn (string $date): int => (int) $storedAmounts->get($date, $tontine->daily_amount)));
    }

    public function save(): void
    {
        $tenantId = $this->tenantId();
        $data = $this->validate([
            'customerId' => ['required', 'integer', Rule::exists('customers', 'id')->where('tenant_id', $tenantId)->whereNull('deleted_at')],
            'tontineId' => ['required', 'integer', Rule::exists('tontines', 'id')->where('tenant_id', $tenantId)->where('customer_id', $this->customerId)->where('status', 'active')],
            'paymentDate' => $this->customSelection ? ['nullable'] : ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'paymentEndDate' => $this->customSelection ? ['nullable'] : ['nullable', 'date_format:Y-m-d', 'before_or_equal:today', 'after_or_equal:paymentDate'],
            'selectedDates' => $this->customSelection ? ['required', 'array', 'min:1', 'max:366'] : ['array'],
            'selectedDates.*' => $this->customSelection ? ['required', 'string', 'date_format:Y-m-d', 'distinct', 'before_or_equal:today', 'before_or_equal:receivedDate'] : [],
            'receivedDate' => $this->customSelection ? ['required', 'date_format:Y-m-d', 'before_or_equal:today'] : ['required', 'date_format:Y-m-d', 'before_or_equal:today', 'after_or_equal:'.($this->paymentEndDate ?: $this->paymentDate)],
            'amount' => ['required', 'integer', 'min:1', 'max:1000000000'],
        ], [
            'selectedDates.required' => 'Sélectionnez au moins une journée à régler.',
            'selectedDates.min' => 'Sélectionnez au moins une journée à régler.',
            'selectedDates.max' => 'Sélectionnez au maximum 366 journées.',
            'selectedDates.*.date_format' => 'Une journée sélectionnée est invalide.',
            'selectedDates.*.distinct' => 'Une journée ne peut pas être sélectionnée deux fois.',
            'selectedDates.*.before_or_equal' => 'Les journées doivent être passées ou actuelles et ne pas dépasser la date du versement.',
            'customerId.required' => 'Sélectionnez un client.',
            'customerId.exists' => 'Sélectionnez un client de votre espace.',
            'tontineId.required' => 'Sélectionnez une tontine.',
            'tontineId.exists' => 'Sélectionnez une tontine en cours appartenant à ce client.',
            'paymentDate.required' => 'Indiquez la date de cotisation.',
            'paymentDate.date_format' => 'La date de cotisation est invalide.',
            'paymentDate.before_or_equal' => 'La date ne peut pas être dans le futur.',
            'paymentEndDate.date_format' => 'La date de fin est invalide.',
            'paymentEndDate.before_or_equal' => 'La dernière journée ne peut pas être dans le futur.',
            'paymentEndDate.after_or_equal' => 'La dernière journée doit suivre ou correspondre à la première.',
            'receivedDate.required' => 'Indiquez la date réelle du versement.',
            'receivedDate.date_format' => 'La date du versement est invalide.',
            'receivedDate.before_or_equal' => 'La date du versement ne peut pas être dans le futur.',
            'receivedDate.after_or_equal' => 'Le versement ne peut pas précéder la dernière journée cotisée.',
            'amount.required' => 'Renseignez le montant versé.',
            'amount.integer' => 'Le montant doit être un nombre entier de GNF.',
            'amount.min' => 'Le montant doit être supérieur à zéro.',
            'amount.max' => 'Le montant dépasse la limite autorisée.',
        ]);

        $dates = $this->paymentDates();
        if ($dates === []) {
            throw ValidationException::withMessages(['paymentEndDate' => 'Sélectionnez au maximum 366 journées par enregistrement.']);
        }
        foreach ($dates as $date) {
            if (CarbonImmutable::parse($date)->isSunday()) {
                throw ValidationException::withMessages([($this->customSelection ? 'selectedDates' : 'paymentDate') => 'Les dimanches sont exclus des cotisations.']);
            }
        }
        DB::transaction(function () use ($tenantId, $data, $dates): void {
            $tontine = $this->tontinesQuery()->where('customer_id', $data['customerId'])->lockForUpdate()->findOrFail($data['tontineId']);
            if ($dates[0] < $tontine->start_date->toDateString() || end($dates) > $tontine->end_date->toDateString()) {
                throw ValidationException::withMessages([($this->customSelection ? 'selectedDates' : 'paymentDate') => 'Toutes les dates doivent être comprises dans la période de la tontine.']);
            }
            $days = $tontine->days()->where('tenant_id', $tenantId)->whereIn('due_date', $dates)->get()
                ->keyBy(fn ($day): string => $day->due_date->toDateString());
            $existingPayments = TontinePayment::query()->where('tenant_id', $tenantId)->whereIn('tontine_day_id', $days->pluck('id'))
                ->with('day', 'recorder')->get()->keyBy('tontine_day_id');
            $expectedAmount = (int) collect($dates)->sum(fn (string $date): int => (int) ($days->get($date)?->amount ?? $tontine->daily_amount));
            if ((int) $data['amount'] !== $expectedAmount) {
                throw ValidationException::withMessages(['amount' => 'Le montant doit correspondre aux journées sélectionnées : '.number_format($expectedAmount, 0, ',', ' ').' GNF.']);
            }
            foreach ($dates as $date) {
                $day = $days->get($date);
                $payment = $day === null ? null : $existingPayments->get($day->id);
                if ($payment !== null && $payment->voided_at === null) {
                    throw ValidationException::withMessages([($this->customSelection ? 'selectedDates' : 'paymentDate') => 'Une cotisation est déjà enregistrée pour cette tontine à la date du '.CarbonImmutable::parse($date)->format('d/m/Y').'. Aucun versement du lot n’a été enregistré.']);
                }
            }
            foreach ($dates as $date) {
                $day = $days->get($date) ?? $tontine->days()->create(['tenant_id' => $tenantId, 'due_date' => $date, 'amount' => $tontine->daily_amount]);
                $payment = $existingPayments->get($day->id) ?? new TontinePayment;
                $history = $payment->auditHistory();
                $payment->fill([
                    'tenant_id' => $tenantId,
                    'tontine_id' => $tontine->id,
                    'tontine_day_id' => $day->id,
                    'amount' => $day->amount,
                    'payment_date' => $data['receivedDate'],
                    'recorded_by' => auth()->id(),
                    'voided_at' => null,
                    'history' => $history,
                ]);
                $payment->appendHistory('recorded', auth()->user(), $date);
                $payment->save();
            }
        });

        $this->reset('customerId', 'tontineId', 'amount', 'paymentEndDate', 'selectedMonth', 'selectedDates', 'customSelection', 'periodStart', 'periodEnd');
        $this->receivedDate = now()->toDateString();
        $this->paymentDate = now()->toDateString();
        $this->resetValidation();
        $this->resetPage();
        session()->flash('success', count($dates) === 1 ? 'La cotisation a été enregistrée avec succès.' : 'Les '.count($dates).' journées de cotisation ont été enregistrées avec succès.');
        $this->dispatch('payment-updated');
    }

    public function confirmCancellation(int $paymentId): void
    {
        $payment = $this->paymentsQuery()->whereNull('voided_at')->findOrFail($paymentId);
        $this->cancellingId = $payment->id;
        $this->cancellationReason = '';
        $this->resetValidation();
    }

    public function dismissCancellation(): void
    {
        $this->reset('cancellingId', 'cancellationReason');
        $this->resetValidation('cancellationReason');
    }

    public function cancelPayment(): void
    {
        $tenantId = $this->tenantId();
        abort_if($this->cancellingId === null, 404);
        $payment = $this->paymentsQuery()->findOrFail($this->cancellingId);
        $this->cancellationReason = trim($this->cancellationReason);
        $this->validate(['cancellationReason' => ['required', 'string', 'min:5', 'max:500']], [
            'cancellationReason.required' => 'Indiquez le motif de l’annulation.',
            'cancellationReason.min' => 'Le motif doit contenir au moins 5 caractères.',
            'cancellationReason.max' => 'Le motif ne doit pas dépasser 500 caractères.',
        ]);

        DB::transaction(function () use ($tenantId, $payment): void {
            Tontine::query()->where('tenant_id', $tenantId)->lockForUpdate()->findOrFail($payment->tontine_id);
            $payment = $this->paymentsQuery()->lockForUpdate()->findOrFail($payment->id);
            if ($payment->voided_at !== null) {
                throw ValidationException::withMessages(['cancellationReason' => 'Cette cotisation est déjà annulée.']);
            }
            $payment->appendHistory('cancelled', auth()->user(), $payment->day->due_date->toDateString(), $this->cancellationReason);
            $payment->voided_at = now();
            $payment->save();
        });

        $this->dismissCancellation();
        session()->flash('success', 'La cotisation a été annulée. Son historique est conservé.');
        $this->dispatch('payment-updated');
    }

    private function tontinesQuery(): Builder
    {
        return Tontine::query()->where('tenant_id', $this->tenantId())->where('status', 'active')
            ->whereHas('customer', fn (Builder $query): Builder => $query->where('tenant_id', $this->tenantId()));
    }

    private function paymentsQuery(): Builder
    {
        $tenantId = $this->tenantId();

        return TontinePayment::query()->where('tenant_id', $tenantId)
            ->whereHas('tontine', fn (Builder $query): Builder => $query->where('tenant_id', $tenantId)->whereHas('customer', fn (Builder $client): Builder => $client->where('tenant_id', $tenantId)))
            ->whereHas('day', fn (Builder $query): Builder => $query->where('tenant_id', $tenantId)->whereColumn('tontine_days.tontine_id', 'tontine_payments.tontine_id'));
    }

    public function render(): View
    {
        $tenantId = $this->tenantId();

        $batchDates = $this->paymentDates();
        $batchPaidCount = $this->paymentsQuery()->where('tontine_id', $this->tontineId)->whereNull('voided_at')->whereHas('day', fn (Builder $query): Builder => $query->whereIn('due_date', $batchDates))->count();

        abort_unless($this->filterMonth === '' || Validator::make(['month' => $this->filterMonth], ['month' => ['required', 'date_format:Y-m']])->passes(), 422);
        $monthStart = $this->filterMonth === '' ? null : CarbonImmutable::createFromFormat('!Y-m', $this->filterMonth);
        $payments = $this->paymentsQuery()->with('tontine.customer', 'day', 'recorder')->when($this->search !== '', fn (Builder $query): Builder => $query->whereHas('tontine', fn (Builder $query): Builder => $query->where(fn (Builder $query): Builder => $query->where('reference', 'like', '%'.$this->search.'%')->orWhereHas('customer', fn (Builder $query): Builder => $query->where('name', 'like', '%'.$this->search.'%')))))->when($this->filterDate !== '', fn (Builder $query): Builder => $query->whereDate('payment_date', $this->filterDate))->when($monthStart !== null, fn (Builder $query): Builder => $query->whereHas('day', fn (Builder $query): Builder => $query->whereBetween('due_date', [$monthStart->toDateString(), $monthStart->endOfMonth()->toDateString()])))->latest('id')->paginate(10);
        $paymentMonths = $payments->getCollection()->groupBy(fn (TontinePayment $payment): string => $payment->day->due_date->format('Y-m'))->sortKeysDesc();

        $selectedTontine = $this->selectedTontine();
        $calendarDays = $selectedTontine === null ? [] : $this->calendarDays($selectedTontine);

        return view('livewire.cotisation', [
            'batchDates' => $batchDates,
            'selectedPeriods' => $this->selectedPeriods($batchDates),
            'calendarDays' => $calendarDays,
            'selectedTontine' => $selectedTontine,
            'batchPaidCount' => $batchPaidCount,
            'clients' => Customer::query()->where('tenant_id', $tenantId)->whereHas('tontines', fn (Builder $query): Builder => $query->where('tenant_id', $tenantId)->where('status', 'active'))->orderBy('name')->get(),
            'tontines' => Tontine::query()->whereHas('customer', fn (Builder $query): Builder => $query->where('tenant_id', $this->tenantId()))->where('tenant_id', $tenantId)->where('customer_id', $this->customerId)->where('status', 'active')->orderBy('reference')->get(),
            'paymentToCancel' => $this->cancellingId === null ? null : $this->paymentsQuery()->with('tontine.customer', 'day')->find($this->cancellingId),
            'payments' => $payments,
            'paymentMonths' => $paymentMonths,
            'displayedTotal' => (int) $payments->whereNull('voided_at')->sum('amount'),
        ])->layout('components.layouts.backend');
    }

    private function tenantId(): int
    {
        abort_unless(auth()->check(), 403);

        return auth()->user()->activeTenantId();
    }
}
