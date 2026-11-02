<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Tontine as TontineModel;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Tontine extends Component
{
    use WithPagination;

    public string $customerId = '';

    public string $dailyAmount = '';

    public string $month = '';

    public string $startDate = '';

    public string $endDate = '';

    public string $status = 'active';

    public string $statusFilter = 'all';

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedMonth(): void
    {
        $this->validateOnly('month', ['month' => ['required', 'date_format:Y-m']], ['month.date_format' => 'Choisissez un mois valide.']);
        $start = CarbonImmutable::createFromFormat('!Y-m', $this->month);
        $this->startDate = $start->isSameMonth(today()) ? today()->toDateString() : $start->toDateString();
        $this->endDate = TontineModel::contributionEndDate($this->startDate)->toDateString();
    }

    public function updatedStartDate(): void
    {
        if (Validator::make(['date' => $this->startDate], ['date' => ['required', 'date_format:Y-m-d']])->fails()) {
            $this->endDate = '';

            return;
        }

        $start = CarbonImmutable::createFromFormat('!Y-m-d', $this->startDate);
        $this->month = $start->format('Y-m');
        $this->endDate = TontineModel::contributionEndDate($this->startDate)->toDateString();
    }

    public function save(): void
    {
        $tenantId = $this->tenantId();
        $data = $this->validate([
            'customerId' => ['required', 'integer', Rule::exists('customers', 'id')->where('tenant_id', $tenantId)->whereNull('deleted_at')],
            'dailyAmount' => ['required', 'integer', 'min:1', 'max:1000000000'],
            'month' => ['nullable', 'date_format:Y-m'],
            'startDate' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'endDate' => ['required', 'date_format:Y-m-d', 'after_or_equal:startDate'],
            'status' => ['required', Rule::in(['active', 'closed'])],
        ], [
            'customerId.required' => 'Sélectionnez un client.',
            'customerId.exists' => 'Sélectionnez un client de votre espace.',
            'dailyAmount.required' => 'Renseignez le montant quotidien.',
            'dailyAmount.integer' => 'Le montant doit être un nombre entier de GNF.',
            'dailyAmount.min' => 'Le montant doit être supérieur à zéro.',
            'dailyAmount.max' => 'Le montant dépasse la limite autorisée.',
            'startDate.required' => 'Indiquez la date de début.',
            'startDate.date_format' => 'La date de début est invalide.',
            'startDate.after_or_equal' => 'La tontine ne peut pas commencer avant aujourd’hui.',
            'endDate.required' => 'Indiquez la date de fin.',
            'endDate.date_format' => 'La date de fin est invalide.',
            'endDate.after_or_equal' => 'La date de fin doit suivre la date de début.',
            'status.in' => 'Choisissez un statut valide.',
        ]);

        $data['endDate'] = TontineModel::contributionEndDate($data['startDate'])->toDateString();
        $this->endDate = $data['endDate'];

        $tontine = DB::transaction(function () use ($tenantId, $data): TontineModel {
            $customer = Customer::query()->where('tenant_id', $tenantId)->whereNull('deleted_at')->lockForUpdate()->findOrFail($data['customerId']);

            do {
                $reference = 'TN-'.Str::upper(Str::random(7));
            } while (TontineModel::query()->where('tenant_id', $tenantId)->where('reference', $reference)->exists());

            return TontineModel::query()->create([
                'tenant_id' => $tenantId,
                'customer_id' => $customer->id,
                'reference' => $reference,
                'daily_amount' => $data['dailyAmount'],
                'start_date' => $data['startDate'],
                'end_date' => $data['endDate'],
                'status' => $data['status'],
                'closed_at' => $data['status'] === 'closed' ? now() : null,
            ]);
        });

        $this->reset('customerId', 'dailyAmount', 'month', 'startDate', 'endDate', 'status');
        $this->resetValidation();
        $this->resetPage();
        session()->flash('success', 'La tontine '.$tontine->reference.' a été créée avec succès.');
    }

    public function render(): View
    {
        return view('livewire.tontine', [
            'customers' => Customer::query()->where('tenant_id', $this->tenantId())->whereNull('deleted_at')->orderBy('name')->get(),
            'tontines' => TontineModel::query()->where('tenant_id', $this->tenantId())->whereHas('customer', fn (Builder $query): Builder => $query->where('tenant_id', $this->tenantId()))->when($this->statusFilter === 'ongoing', fn (Builder $query): Builder => $query->where('status', 'active')->whereDate('end_date', '>=', today()))->when($this->statusFilter === 'finished', fn (Builder $query): Builder => $query->where(fn (Builder $query): Builder => $query->where('status', 'closed')->orWhereDate('end_date', '<', today())))->with('customer')->withSum(['payments as paid_amount' => fn (Builder $query): Builder => $query->where('tenant_id', $this->tenantId())->whereNull('voided_at')], 'amount')->latest('id')->paginate(10),
        ])->layout('components.layouts.backend');
    }

    private function tenantId(): int
    {
        abort_unless(auth()->check(), 403);

        return auth()->user()->activeTenantId();
    }
}
