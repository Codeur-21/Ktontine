<?php

namespace App\Filament\Pages;

use App\Models\EmailDelivery;
use App\Models\PlatformSetting;
use App\Models\User;
use App\PlatformMail;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Throwable;

class Communication extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $navigationLabel = 'Communication';

    protected static ?string $title = 'Communication avec les tontiniers';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.communication';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    /** @var array<string, mixed>|null */
    #[Locked]
    public ?array $prepared = null;

    /** @var list<array{name: string, email: string, status: string}> */
    #[Locked]
    public array $results = [];

    #[Locked]
    public ?string $lastBatchId = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->isPlatformAdmin() ?? false;
    }

    private function authorizeCommunication(): void
    {
        abort_unless(auth()->user()?->fresh()?->isPlatformAdmin(), 403);
        Gate::authorize('view-platform-administration');
    }

    public function mount(): void
    {
        $this->authorizeCommunication();
        $defaults = ['mode' => 'marketing', 'recipientIds' => [], 'recipientId' => null, 'subject' => '', 'messageBody' => '', 'buttonLabel' => '', 'buttonUrl' => ''];
        if (request()->filled('recipient')) {
            $recipient = $this->recipientsQuery('individual')->findOrFail(request()->integer('recipient'));
            $defaults['mode'] = 'individual';
            $defaults['recipientId'] = (string) $recipient->id;
        }
        $this->form->fill($defaults);
        $this->lastBatchId = session()->get('communication.batch.'.auth()->id());
        $this->refreshResults();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('À qui souhaitez-vous écrire ?')->icon('heroicon-o-user-group')->components([
                ToggleButtons::make('mode')->label('Type de message')->options(['marketing' => 'Mail marketing', 'individual' => 'Message individuel'])
                    ->icons(['marketing' => 'heroicon-o-megaphone', 'individual' => 'heroicon-o-envelope'])->inline()->required()->live()
                    ->afterStateUpdated(function (): void {
                        $this->prepared = null;
                    }),
                Select::make('recipientIds')->label('Tontiniers destinataires')->multiple()->searchable()->preload()->required()->maxItems(20)
                    ->options(fn (): array => $this->recipientOptions('marketing'))
                    ->getSearchResultsUsing(fn (string $search): array => $this->recipientOptions('marketing', $search))
                    ->getOptionLabelsUsing(fn (array $values): array => $this->recipientOptions('marketing', ids: $values))
                    ->visible(fn (Get $get): bool => $get('mode') === 'marketing')
                    ->helperText('Recherchez par nom, espace ou e-mail. Jusqu’à 20 propriétaires actifs d’espaces actifs par envoi.'),
                Select::make('recipientId')->label('Choisir un tontinier')->searchable()->preload()->required()
                    ->options(fn (): array => $this->recipientOptions('individual'))
                    ->getSearchResultsUsing(fn (string $search): array => $this->recipientOptions('individual', $search))
                    ->getOptionLabelUsing(fn ($value): ?string => $this->recipientOptions('individual', ids: [$value])[$value] ?? null)
                    ->visible(fn (Get $get): bool => $get('mode') === 'individual')
                    ->helperText('Vous pouvez aussi écrire à un propriétaire dont le compte ou l’espace est inactif.'),
            ]),
            Section::make('Votre message')->icon('heroicon-o-pencil-square')->components([
                TextInput::make('subject')->label('Objet du mail')->required()->maxLength(150)->regex('/^[^\r\n]+$/')->prefixIcon('heroicon-o-envelope'),
                Textarea::make('messageBody')->label('Message')->required()->maxLength(10000)->rows(10)
                    ->helperText('Texte simple, sans HTML. Utilisez {nom} et {espace} pour personnaliser chaque mail.'),
                TextInput::make('buttonLabel')->label('Texte du bouton (facultatif)')->maxLength(60)->requiredWith('buttonUrl')->prefixIcon('heroicon-o-cursor-arrow-rays'),
                TextInput::make('buttonUrl')->label('Lien du bouton (facultatif)')->url()->rules(['nullable', 'url:http,https'])->maxLength(2048)->requiredWith('buttonLabel')->prefixIcon('heroicon-o-link'),
            ]),
        ])->statePath('data');
    }

    private function recipientsQuery(string $mode): Builder
    {
        return User::query()->whereNotNull('tenant_id')->where('role', 'owner')->whereHas('tenant')
            ->when($mode === 'marketing', fn (Builder $query): Builder => $query->where('is_active', true)->whereHas('tenant', fn (Builder $tenant): Builder => $tenant->where('is_active', true)));
    }

    /** @param list<int|string> $ids
     * @return array<int, string>
     */
    private function recipientOptions(string $mode, string $search = '', array $ids = []): array
    {
        $this->authorizeCommunication();

        return $this->recipientsQuery($mode)->with('tenant:id,name')
            ->when($ids !== [], fn (Builder $query): Builder => $query->whereKey($ids))
            ->when($search !== '', fn (Builder $query): Builder => $query->where(fn (Builder $query): Builder => $query
                ->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%')
                ->orWhereHas('tenant', fn (Builder $tenant): Builder => $tenant->where('name', 'like', '%'.$search.'%'))))
            ->orderBy('name')->limit(50)->get()->mapWithKeys(fn (User $user): array => [$user->id => $user->tenant->name.' · '.$user->name.' · '.$user->email])->all();
    }

    public function newsTemplateAction(): Action
    {
        return $this->templateAction('newsTemplate', 'Une nouveauté', 'heroicon-o-sparkles', 'news');
    }

    public function offerTemplateAction(): Action
    {
        return $this->templateAction('offerTemplate', 'Une offre', 'heroicon-o-gift', 'offer');
    }

    public function personalTemplateAction(): Action
    {
        return $this->templateAction('personalTemplate', 'Un message personnel', 'heroicon-o-chat-bubble-left-right', 'personal');
    }

    private function templateAction(string $name, string $label, string $icon, string $template): Action
    {
        return Action::make($name)->label($label)->icon($icon)->color('gray')
            ->requiresConfirmation()
            ->modalIcon($icon)
            ->modalHeading('Utiliser le modèle « '.$label.' » ?')
            ->modalDescription('L’objet et le message actuels seront remplacés par ce modèle. Vos destinataires et le bouton du mail seront conservés. Aucun mail ne sera envoyé.')
            ->modalSubmitActionLabel('Utiliser ce modèle')
            ->modalCancelActionLabel('Garder mon message')
            ->action(fn () => $this->useTemplate($template));
    }

    public function useTemplate(string $template): void
    {
        $this->authorizeCommunication();
        $templates = [
            'news' => ['Une nouveauté vous attend sur Ktontine', 'Bonjour {nom},'."\n\n".'Nous avons une nouveauté à vous présenter pour votre espace {espace}.'."\n\n".'[Présentez ici la nouveauté et son intérêt pour le tontinier.]'],
            'offer' => ['Une offre pour votre activité', 'Bonjour {nom},'."\n\n".'Nous avons préparé une offre pour accompagner votre activité avec {espace}.'."\n\n".'[Précisez ici l’offre, ses conditions et sa date de fin.]'],
            'personal' => ['Un message concernant votre espace Ktontine', 'Bonjour {nom},'."\n\n".'Je vous contacte au sujet de votre espace {espace}.'."\n\n".'[Rédigez ici votre message personnel.]'],
        ];
        abort_unless(isset($templates[$template]), 422);
        $this->data['subject'] = $templates[$template][0];
        $this->data['messageBody'] = $templates[$template][1];
        $this->prepared = null;
        $this->resetValidation();
    }

    public function prepare(): void
    {
        $this->authorizeCommunication();
        $data = $this->form->getState();
        $mode = $data['mode'];
        abort_unless(in_array($mode, ['marketing', 'individual'], true), 422);
        $ids = $mode === 'individual' ? [$data['recipientId']] : ($data['recipientIds'] ?? []);
        if ($ids === [] || count($ids) > 20 || count(array_unique($ids)) !== count($ids)) {
            throw ValidationException::withMessages(['data.recipientIds' => 'Choisissez entre 1 et 20 tontiniers distincts.']);
        }
        $recipients = $this->recipientsQuery($mode)->whereKey($ids)->with('tenant:id,name')->get();
        if ($recipients->count() !== count($ids) || $recipients->contains(fn (User $user): bool => ! filter_var($user->email, FILTER_VALIDATE_EMAIL))) {
            throw ValidationException::withMessages(['data.'.($mode === 'individual' ? 'recipientId' : 'recipientIds') => 'Un destinataire n’est plus disponible. Vérifiez votre sélection.']);
        }
        $this->prepared = [
            'token' => (string) Str::uuid(), 'platformName' => PlatformSetting::current()->name, 'mode' => $mode, 'subject' => trim($data['subject']), 'messageBody' => trim($data['messageBody']),
            'buttonLabel' => $data['buttonLabel'] ?? '', 'buttonUrl' => $data['buttonUrl'] ?? '',
            'recipients' => $recipients->map(fn (User $user): array => ['id' => $user->id, 'tenant_id' => $user->tenant_id, 'name' => $user->name, 'email' => $user->email, 'businessName' => $user->tenant->name])->all(),
        ];
        $this->dispatch('communication-preview');
    }

    public function sendPrepared(): void
    {
        $this->authorizeCommunication();
        abort_unless($this->prepared !== null, 422);
        $prepared = $this->prepared;
        $users = $this->recipientsQuery($prepared['mode'])->whereKey(array_column($prepared['recipients'], 'id'))->get()->keyBy('id');
        foreach ($prepared['recipients'] as $recipient) {
            if (! isset($users[$recipient['id']]) || $users[$recipient['id']]->email !== $recipient['email']) {
                $this->prepared = null;
                $this->dispatch('communication-finished');
                Notification::make()->warning()->title('La liste des destinataires a changé')->body('Préparez un nouvel aperçu avant l’envoi.')->send();

                return;
            }
        }
        $lock = Cache::lock('communication.send.'.auth()->id(), 360);
        if (! $lock->get()) {
            Notification::make()->warning()->title('Un envoi est déjà en cours')->send();

            return;
        }
        try {
            $key = 'communication.rate.'.auth()->id();
            if (RateLimiter::tooManyAttempts($key, 5)) {
                Notification::make()->warning()->title('Patientez avant un nouvel envoi')->body('Vous pouvez préparer jusqu’à 5 envois par minute.')->send();

                return;
            }
            if (! Cache::add('communication.batch.'.$prepared['token'], true, now()->addDay())) {
                Notification::make()->warning()->title('Cet envoi a déjà été traité')->send();

                return;
            }
            RateLimiter::hit($key, 60);
            try {
                app(PlatformMail::class)->communication(auth()->user(), $prepared);
            } catch (Throwable $exception) {
                Cache::forget('communication.batch.'.$prepared['token']);
                report($exception);
                Notification::make()->danger()->title('L’envoi n’a pas pu être programmé')->body('Votre message est conservé. Réessayez dans un instant.')->send();

                return;
            }
            $this->lastBatchId = $prepared['token'];
            session()->put('communication.batch.'.auth()->id(), $this->lastBatchId);
            $this->prepared = null;
            $this->refreshResults();
            Notification::make()->title('Mails mis en attente')
                ->body(count($this->results).' mail(s) seront envoyés en arrière-plan. Le bilan se met à jour automatiquement.')
                ->success()->duration(10000)->send();
            $this->dispatch('communication-finished');
        } finally {
            $lock->release();
        }
    }

    public function refreshResults(): void
    {
        $this->authorizeCommunication();
        $this->results = $this->lastBatchId === null ? [] : EmailDelivery::query()
            ->where('sender_id', auth()->id())->where('batch_id', $this->lastBatchId)->orderBy('id')->limit(20)
            ->get()->map(fn (EmailDelivery $delivery): array => ['name' => $delivery->name, 'email' => $delivery->email, 'status' => $delivery->status])->all();
    }
}
