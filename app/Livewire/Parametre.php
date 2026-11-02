<?php

namespace App\Livewire;

use App\Models\PlatformSetting;
use App\Models\Setting;
use App\TicketSheet;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Parametre extends Component
{
    public string $businessName = '';

    public string $contactPhone = '';

    public string $contactEmail = '';

    public string $address = '';

    public string $ticketFooter = '';

    public string $ticketPaper = 'A4';

    /** @var array<string, mixed> */
    public array $ticketPreferences = TicketSheet::PREFERENCES;

    public bool $showSignatures = true;

    public bool $showCommission = true;

    public string $priorityDays = '7';

    public string $reportPeriod = 'month';

    public string $currentPassword = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        $this->loadSettings();
    }

    public function cancelChanges(): void
    {
        $this->loadSettings();
        $this->resetValidation();
    }

    public function defaultPreferences(): void
    {
        $this->authorizeOwner();
        $this->reset('ticketFooter', 'ticketPaper', 'showSignatures', 'showCommission', 'priorityDays', 'reportPeriod');
        $this->ticketPreferences = PlatformSetting::current()->ticket_preferences;
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->authorizeOwner();
        $this->businessName = trim($this->businessName);
        $this->contactPhone = trim($this->contactPhone);
        $this->contactEmail = trim($this->contactEmail);
        $this->address = trim($this->address);
        $this->ticketFooter = trim($this->ticketFooter);
        $platform = PlatformSetting::current();
        $this->ticketPreferences = $platform->ticket_preferences;
        $ticketRules = [];
        foreach (array_intersect_key(TicketSheet::rules(), TicketSheet::PREFERENCES) as $key => $rules) {
            $ticketRules['ticketPreferences.'.$key] = $rules;
        }
        $ticketMessages = [];
        foreach (TicketSheet::messages() as $key => $message) {
            $ticketMessages['ticketPreferences.'.$key] = $message;
        }
        $data = $this->validate([
            ...$ticketRules,
            'ticketPreferences' => ['required', 'array:'.implode(',', array_keys(TicketSheet::PREFERENCES))],
            'businessName' => ['required', 'string', 'max:100'],
            'contactPhone' => ['nullable', 'string', 'max:25', 'regex:/^\+?[0-9][0-9 ()-]{5,23}[0-9]$/'],
            'contactEmail' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'ticketFooter' => ['nullable', 'string', 'max:300'],
            'ticketPaper' => ['required', Rule::in(['A4', 'A5'])],
            'showSignatures' => ['boolean'],
            'showCommission' => ['boolean'],
            'priorityDays' => ['required', 'integer', 'min:1', 'max:365'],
            'reportPeriod' => ['required', Rule::in(['month', 'week', 'today'])],
        ], [
            ...$ticketMessages,
            'businessName.required' => 'Indiquez le nom de votre espace.',
            'businessName.max' => 'Le nom ne doit pas dépasser 100 caractères.',
            'contactPhone.max' => 'Le numéro ne doit pas dépasser 25 caractères.',
            'contactPhone.regex' => 'Indiquez un numéro de téléphone valide (7 à 25 caractères).',
            'contactEmail.email' => 'Indiquez une adresse e-mail valide.',
            'contactEmail.max' => 'L’e-mail ne doit pas dépasser 255 caractères.',
            'address.max' => 'L’adresse ne doit pas dépasser 255 caractères.',
            'ticketFooter.max' => 'Le message ne doit pas dépasser 300 caractères.',
            'ticketPaper.in' => 'Choisissez le format A4 ou A5.',
            'priorityDays.required' => 'Indiquez le seuil de priorité.',
            'priorityDays.integer' => 'Le seuil doit être un nombre entier de journées.',
            'priorityDays.min' => 'Le seuil doit être d’au moins une journée.',
            'priorityDays.max' => 'Le seuil ne peut pas dépasser 365 journées.',
            'reportPeriod.in' => 'Choisissez une période proposée.',
        ]);
        DB::transaction(function () use ($data): void {
            $tenant = auth()->user()->tenant()->lockForUpdate()->firstOrFail();
            $tenant->update(['name' => $data['businessName']]);
            $attributes = [
                'contact_phone' => $data['contactPhone'] ?? '', 'contact_email' => $data['contactEmail'] ?? '',
                'address' => $data['address'] ?? '', 'ticket_footer' => $data['ticketFooter'] ?? '',
                'ticket_paper' => $data['ticketPaper'], 'show_signatures' => $data['showSignatures'],
                'show_commission' => $data['showCommission'], 'priority_days' => (int) $data['priorityDays'],
                'report_period' => $data['reportPeriod'],
            ];
            Setting::current()->fill($attributes)->save();
        });
        auth()->user()->unsetRelation('tenant');
        $this->loadSettings();
        session()->flash('success', 'Les paramètres de votre espace ont été enregistrés.');
    }

    public function changePassword(): void
    {
        $this->tenantId();
        $key = 'settings-password:'.auth()->id().'|'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->reset('currentPassword', 'password', 'password_confirmation');
            throw ValidationException::withMessages(['currentPassword' => 'Trop de tentatives. Réessayez dans '.RateLimiter::availableIn($key).' secondes.']);
        }
        try {
            $data = $this->validate([
                'currentPassword' => ['required', 'current_password'],
                'password' => ['required', 'string', 'confirmed', 'different:currentPassword', Password::min(8)->mixedCase()->numbers()->symbols()],
            ], [
                'currentPassword.required' => 'Indiquez votre mot de passe actuel.',
                'currentPassword.current_password' => 'Le mot de passe actuel est incorrect.',
                'password.required' => 'Indiquez un nouveau mot de passe.',
                'password.different' => 'Le nouveau mot de passe doit être différent de l’actuel.',
                'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
                'password.min' => 'Utilisez au moins 8 caractères.',
                'password.mixed' => 'Ajoutez une majuscule et une minuscule.',
                'password.numbers' => 'Ajoutez au moins un chiffre.',
                'password.symbols' => 'Ajoutez au moins un caractère spécial.',
            ]);
        } catch (ValidationException $exception) {
            if (array_key_exists('currentPassword', $exception->errors())) {
                RateLimiter::hit($key, 60);
            }
            $this->reset('currentPassword', 'password', 'password_confirmation');
            throw $exception;
        }
        $user = auth()->user();
        $sessionId = session()->getId();
        DB::transaction(function () use ($user, $data, $sessionId): void {
            $user->forceFill(['password' => $data['password'], 'remember_token' => Str::random(60)])->save();
            DB::table('sessions')->where('user_id', $user->id)->where('id', '!=', $sessionId)->delete();
        });
        RateLimiter::clear($key);
        session()->regenerate();
        session()->put('password_hash_web', $user->getAuthPassword());
        $this->reset('currentPassword', 'password', 'password_confirmation');
        $this->resetValidation();
        session()->flash('passwordSuccess', 'Votre mot de passe a été modifié et les autres sessions ont été déconnectées.');
    }

    public function cancelPassword(): void
    {
        $this->reset('currentPassword', 'password', 'password_confirmation');
        $this->resetValidation(['currentPassword', 'password', 'password_confirmation']);
    }

    private function loadSettings(): void
    {
        $settings = Setting::current();
        $this->businessName = auth()->user()->tenant->name;
        $this->contactPhone = $settings->contact_phone;
        $this->contactEmail = $settings->contact_email;
        $this->address = $settings->address;
        $this->ticketFooter = $settings->ticket_footer;
        $this->ticketPaper = $settings->ticket_paper;
        $this->ticketPreferences = array_intersect_key(TicketSheet::defaults($this->businessName, $settings), TicketSheet::PREFERENCES);
        $this->showSignatures = $settings->show_signatures;
        $this->showCommission = $settings->show_commission;
        $this->priorityDays = (string) $settings->priority_days;
        $this->reportPeriod = $settings->report_period;
    }

    private function tenantId(): int
    {
        abort_unless(auth()->check(), 403);

        return auth()->user()->activeTenantId();
    }

    private function authorizeOwner(): void
    {
        $this->tenantId();
        abort_unless(auth()->user()->role === 'owner', 403);
    }

    public function render(): View
    {
        $this->tenantId();

        $preview = array_replace(TicketSheet::defaults($this->businessName ?: 'Votre tontine', new Setting), $this->ticketPreferences, [
            'phone' => $this->contactPhone, 'address' => mb_substr($this->address, 0, 60), 'footer' => mb_substr($this->ticketFooter, 0, 120),
        ]);
        $previewValid = Validator::make($preview, TicketSheet::rules())->passes();

        return view('livewire.parametre', [
            'canManageSettings' => auth()->user()->role === 'owner',
            'ticketPalettes' => TicketSheet::PALETTES,
            'ticketPreferencesManagedCentrally' => true,
            'ticketPreview' => $previewValid ? TicketSheet::design($preview) : null,
        ])->layout('components.layouts.backend');
    }
}
