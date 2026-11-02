<?php

namespace App\Filament\Pages;

use App\Models\PlatformSetting;
use App\TicketSheet;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Slider;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;

class PlatformSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $slug = 'parametres';

    protected static ?string $navigationLabel = 'Paramètres centraux';

    protected static ?string $title = 'Paramètres centraux';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.platform-settings';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->isPlatformAdmin() ?? false;
    }

    public function mount(): void
    {
        Gate::authorize('view-platform-administration');
        $this->form->fill(PlatformSetting::current()->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Paramètres de la plateforme')->persistTabInQueryString('onglet')->tabs([
                Tab::make('Identité & contact')->icon('heroicon-o-building-office')->schema([
                    Section::make('Votre marque')->description('Une identité commune au site, au panel et aux documents.')->icon('heroicon-o-cog-6-tooth')->columns(2)->components([
                        FileUpload::make('logo_path')->label('Logo de la plateforme')->image()
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])->maxSize(2048)
                            ->disk('public')->directory('platform/logos')->visibility('public')
                            ->preventFilePathTampering(allowFilePathUsing: fn (string $file): bool => $file === PlatformSetting::current()->logo_path)
                            ->helperText('PNG, JPG ou WebP · 2 Mo maximum. Utilisé dans le panel, le site et les tickets. Retirez-le pour retrouver le logo par défaut.'),
                        TextInput::make('name')->label('Nom de la plateforme')->required()->maxLength(100)->default('Ktontine')->prefixIcon('heroicon-o-building-office'),
                    ]),
                    Section::make('Coordonnées de contact')->description('Affichées dans « Contacter le support » sur le site et dans l’espace tontinier. Laissez un champ vide pour masquer le moyen de contact correspondant.')->icon('heroicon-o-identification')->columns(2)->components([
                        TextInput::make('contact_phone')->label('Téléphone de support')->tel()->maxLength(25)->default('')
                            ->regex('/^\+?[0-9][0-9 ()-]{5,23}[0-9]$/')->prefixIcon('heroicon-o-phone'),
                        TextInput::make('contact_email')->label('E-mail de support')->email()->maxLength(255)->default('')->prefixIcon('heroicon-o-envelope'),
                        TextInput::make('address')->label('Adresse de la plateforme')->maxLength(255)->default('')->prefixIcon('heroicon-o-map-pin')->columnSpanFull(),
                    ]),
                ]),
                Tab::make('Tickets & impression')->icon('heroicon-o-printer')->schema([
                    Section::make('Valeurs par défaut des planches A4')->description('Appliquées à l’ouverture de nouvelles planches, sans modifier les coordonnées des tontiniers.')->icon('heroicon-o-squares-2x2')->columns(2)->components([
                        Select::make('ticket_preferences.style')->label('Modèle de ticket')->options(['classic' => 'Groupe solidaire', 'orange' => 'Tontine espoir', 'minimal' => 'Tontine progrès'])->required()->default('classic'),
                        Select::make('ticket_preferences.color')->label('Couleur')->options(array_map(fn (array $palette): string => $palette['label'], TicketSheet::PALETTES))->required()->default('blue'),
                        Select::make('ticket_preferences.layout')->label('Tickets par feuille A4')->options(['6' => '6 · plus d’espace', '8' => '8 · standard', '10' => '10 · compact', '12' => '12 · petit format'])->required()->default('8'),
                        Toggle::make('ticket_preferences.numberCards')->label('Pré-numéroter les cartes')->default(false)->rules(['boolean'])->helperText('Le premier numéro se choisit avant chaque impression.'),
                    ]),
                    Section::make('Tickets clients et impression')->icon('heroicon-o-printer')->columns(2)->components([
                        Select::make('ticket_paper')->label('Format du ticket client')->options(['A4' => 'A4', 'A5' => 'A5'])->required()->default('A4'),
                        Textarea::make('ticket_footer')->label('Message en bas des tickets')->maxLength(300)->default('')->rows(3),
                        Toggle::make('show_signatures')->label('Afficher les signatures')->default(true)->rules(['boolean']),
                        Toggle::make('show_commission')->label('Afficher la commission prévue')->default(true)->rules(['boolean']),
                    ]),
                ]),
                Tab::make('Filigrane')->icon('heroicon-o-photo')->schema([
                    Section::make('Logo Ktontine en filigrane')->icon('heroicon-o-photo')->description('Petit logo circulaire, limité à 24 mm. Réglages communs à tous les tontiniers, y compris les impressions déjà préparées.')
                        ->columns(2)->components([
                            Toggle::make('ticket_preferences.watermark')->label('Activer le filigrane')->default(true)->rules(['boolean'])->columnSpanFull(),
                            Slider::make('ticket_preferences.watermarkOpacity')->label('Visibilité (%)')->range(minValue: 3, maxValue: 20)->step(1)->tooltips()->default(10)->rules(['integer']),
                            Slider::make('ticket_preferences.watermarkSize')->label('Diamètre du cercle (%)')->range(minValue: 15, maxValue: 30)->step(1)->tooltips()->default(22)->rules(['integer']),
                        ]),
                ]),
                Tab::make('Suivi & rapports')->icon('heroicon-o-chart-bar')->schema([
                    Section::make('Suivi et rapports')->icon('heroicon-o-chart-bar')->columns(2)->components([
                        TextInput::make('priority_days')->label('Seuil de retard prioritaire (jours)')->numeric()->integer()->minValue(1)->maxValue(365)->default(7)->required()->prefixIcon('heroicon-o-clock'),
                        Select::make('report_period')->label('Période des rapports par défaut')->options(['month' => 'Mois en cours', 'week' => 'Semaine en cours', 'today' => 'Aujourd’hui'])->required()->default('month'),
                    ]),
                ]),
            ]),
        ])->columns(1)->statePath('data');
    }

    public function save(): void
    {
        Gate::authorize('view-platform-administration');
        $settings = PlatformSetting::current();
        $previousLogo = $settings->logo_path;
        $data = $this->form->getState();
        foreach (['contact_phone', 'contact_email', 'address', 'ticket_footer'] as $field) {
            $data[$field] ??= '';
        }
        $data['ticket_preferences'] = array_intersect_key($data['ticket_preferences'], TicketSheet::PREFERENCES);
        $settings->fill($data)->save();
        $this->mount();
        Notification::make()->title('Paramètres centraux enregistrés')->success()->send();
        if ($previousLogo !== $settings->logo_path) {
            $this->redirect(static::getUrl());
        }
    }

    public function cancelChanges(): void
    {
        $this->mount();
        $this->resetValidation();
    }

    public function defaultPreferences(): void
    {
        Gate::authorize('view-platform-administration');
        $this->form->fill(array_replace($this->data ?? [], [
            'ticket_preferences' => TicketSheet::PREFERENCES, 'ticket_paper' => 'A4',
            'show_signatures' => true, 'show_commission' => true, 'priority_days' => 7, 'report_period' => 'month',
        ]));
    }
}
