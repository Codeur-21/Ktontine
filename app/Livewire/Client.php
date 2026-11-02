<?php

namespace App\Livewire;

use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class Client extends Component
{
    use WithPagination;

    #[Locked]
    public ?int $editingId = null;

    public $status = 'Actif';

    public $name = '';

    public $address = '';

    public $phone = '';

    public $search = '';
    // public $etat="";

    public function edit(int $customerId): void
    {
        $customer = $this->editableCustomer($customerId);
        $this->resetValidation();
        $this->editingId = $customer->id;
        $this->name = $customer->name;
        $this->phone = $customer->phone ?? '';
        $this->address = $customer->address ?? '';
        $this->status = $customer->status;
        $this->dispatch('client-editing');
    }

    private function editableCustomer(int $customerId): Customer
    {
        abort_unless(auth()->check(), 403);

        return Customer::query()->where('tenant_id', auth()->user()->activeTenantId())->findOrFail($customerId);
    }

    public function fresh(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->fresh();
    }

    public function updatedEtat(): void
    {
        $this->fresh();
    }

    public function annuler(): void
    {
        $this->reset('editingId', 'name', 'address', 'phone', 'status');
        $this->resetValidation();
    }

    public function register(): void
    {

        abort_unless(auth()->check(), 403);
        $tenantId = auth()->user()->activeTenantId();
        $customer = $this->editingId === null ? null : $this->editableCustomer($this->editingId);
        $data = $this->validate([
            'name' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string'],
            'phone' => ['required', 'digits:9'],
            'status' => ['required', Rule::in(Customer::data())],
        ], [
            'name.required' => 'Veillez renseigner le nom du client',
            'name.max' => 'Le nom de caractères est dépassé NB:30',

            'address.required' => 'Adresse du client non remplit',

            'phone.required' => 'Fournissez le numéro du client',
            'phone.digits' => 'Le téléphone doit contenir exactement 9 chiffres.',
            'status.in' => 'Choisissez un statut valide : Actif ou Inactif.',
            'status.required' => 'Choisissez le statut du client.',

        ]);

        if ($customer === null) {
            Customer::create([...$data, 'tenant_id' => $tenantId]);
        } else {
            $customer->update($data);
        }

        $this->annuler();
        $this->fresh();
        session()->flash('customer_action', $customer === null ? 'created' : 'updated');
        session()->flash('success', $customer === null ? 'Le client a été ajouté avec succès.' : 'Les informations du client ont été mises à jour.');
    }

    public function render(): View
    {
        abort_unless(auth()->check(), 403);
        $tenantId = auth()->user()->activeTenantId();
        $search = trim($this->search);
        $clients = Customer::query()->where('tenant_id', $tenantId)->whereNull('deleted_at')->withCount('tontines')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', '%'.$search.'%')->orWhere('phone', 'like', '%'.$search.'%')->orWhere('status', 'like', '%'.$search.'%');
                });
            })->orderBy('name')->orderBy('id')->paginate(10);

        return view('livewire.clients', compact('clients'))->layout('components.layouts.backend');
    }
}
