<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class Equipe extends Component
{
    use WithPagination;

    #[Locked]
    public ?int $editingId = null;

    #[Locked]
    public ?int $deletingId = null;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $role = 'agent';

    public bool $isActive = true;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function edit(int $memberId): void
    {
        $this->authorizeManagement();
        $member = $this->editableMember($memberId);
        $this->resetValidation();
        $this->editingId = $member->id;
        $this->name = $member->name;
        $this->email = $member->email;
        $this->phone = $member->phone;
        $this->role = $member->role;
        $this->isActive = $member->is_active;
        $this->reset('password', 'password_confirmation');
    }

    public function save(): void
    {
        $this->authorizeManagement();
        $member = $this->editingId === null ? null : $this->editableMember($this->editingId);
        $data = $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($member)],
            'phone' => ['required', 'digits:9', Rule::unique('users', 'phone')->ignore($member)],
            'role' => ['required', Rule::in(['manager', 'agent'])],
            'isActive' => ['boolean'],
            'password' => [$member === null ? 'required' : 'nullable', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ], [
            'name.required' => 'Renseignez le nom du membre.',
            'name.max' => 'Le nom ne doit pas dépasser 100 caractères.',
            'email.required' => 'Renseignez une adresse e-mail.',
            'email.email' => 'Renseignez une adresse e-mail valide.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'phone.required' => 'Renseignez le numéro de téléphone.',
            'phone.digits' => 'Le numéro doit contenir exactement 9 chiffres.',
            'phone.unique' => 'Ce numéro est déjà utilisé.',
            'role.in' => 'Choisissez un rôle valide.',
            'password.required' => 'Définissez un mot de passe pour ce membre.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.mixed' => 'Ajoutez une majuscule et une minuscule au mot de passe.',
            'password.numbers' => 'Ajoutez un chiffre au mot de passe.',
            'password.symbols' => 'Ajoutez un caractère spécial au mot de passe.',
        ]);

        DB::transaction(function () use ($member, $data): void {
            $attributes = ['name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone'], 'role' => $data['role'], 'is_active' => $data['isActive']];
            if ($data['password'] !== null && $data['password'] !== '') {
                $attributes['password'] = $data['password'];
            }
            if ($member === null) {
                User::query()->create([...$attributes, 'tenant_id' => $this->tenantId()]);
            } else {
                $member->fill($attributes);
                if (! $data['isActive'] || isset($attributes['password'])) {
                    $member->remember_token = Str::random(60);
                    DB::table('sessions')->where('user_id', $member->id)->delete();
                }
                $member->save();
            }
        });

        $this->cancel();
        $this->resetPage();
        session()->flash('success', $member === null ? 'Le membre a été ajouté à votre équipe.' : 'Le membre a été mis à jour.');
    }

    public function confirmDelete(int $memberId): void
    {
        $this->authorizeManagement();
        $this->deletingId = $this->editableMember($memberId)->id;
    }

    public function delete(): void
    {
        $this->authorizeManagement();
        abort_if($this->deletingId === null, 404);
        $member = $this->editableMember($this->deletingId);
        DB::transaction(function () use ($member): void {
            DB::table('sessions')->where('user_id', $member->id)->delete();
            $member->delete();
        });
        if ($this->editingId === $member->id) {
            $this->cancel();
        }
        $this->deletingId = null;
        $this->resetPage();
        session()->flash('success', 'Le membre a été supprimé de votre équipe.');
    }

    public function cancel(): void
    {
        $this->reset('editingId', 'name', 'email', 'phone', 'password', 'password_confirmation', 'role', 'isActive');
        $this->resetValidation();
    }

    public function cancelDelete(): void
    {
        $this->deletingId = null;
    }

    public function render(): View
    {
        $tenantId = $this->tenantId();
        $search = trim($this->search);

        return view('livewire.equipe', [
            'members' => User::query()->where('tenant_id', $tenantId)
                ->when($search !== '', fn (Builder $query): Builder => $query->where(fn (Builder $query): Builder => $query->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%')->orWhere('phone', 'like', '%'.$search.'%')))
                ->orderBy('name')->orderBy('id')->paginate(10),
            'memberToDelete' => $this->deletingId === null ? null : User::query()->where('tenant_id', $tenantId)->find($this->deletingId),
            'canManageTeam' => auth()->user()->role === 'owner',
        ])->layout('components.layouts.backend');
    }

    private function editableMember(int $memberId): User
    {
        $member = User::query()->where('tenant_id', $this->tenantId())->findOrFail($memberId);
        abort_if($member->id === auth()->id() || $member->role === 'owner', 403);

        return $member;
    }

    private function authorizeManagement(): void
    {
        $this->tenantId();
        abort_unless(auth()->user()->role === 'owner', 403);
    }

    private function tenantId(): int
    {
        abort_unless(auth()->check(), 403);

        return auth()->user()->activeTenantId();
    }
}
