<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations personnelles')
                    ->description('Coordonnées principales de l’utilisateur.')
                    ->icon('heroicon-o-user-circle')
                    ->components([
                        TextInput::make('name')
                            ->label('Nom complet')
                            ->prefixIcon('heroicon-o-user')
                            ->required(),
                        TextInput::make('phone')
                            ->label('Téléphone')
                            ->prefixIcon('heroicon-o-phone')
                            ->tel()
                            ->required()
                            ->length(9)
                            ->rule('digits:9')
                            ->unique(ignoreRecord: true)
                            ->validationMessages([
                                'required' => 'Veuillez renseigner le numéro de téléphone.',
                                'size' => 'Le téléphone doit contenir exactement 9 chiffres.',
                                'digits' => 'Le téléphone doit contenir exactement 9 chiffres.',
                                'unique' => 'Ce numéro de téléphone est déjà associé à un compte.',
                            ]),
                        TextInput::make('email')
                            ->label('Adresse e-mail')
                            ->prefixIcon('heroicon-o-envelope')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),
                        DateTimePicker::make('email_verified_at')
                            ->label('E-mail vérifié le')
                            ->native(false),
                    ])
                    ->columns(2),
                Section::make('Accès et permissions')
                    ->description('Espace, rôle et état du compte.')
                    ->icon('heroicon-o-key')
                    ->components([
                        Select::make('tenant_id')
                            ->disabled(fn (?User $record): bool => $record !== null)
                            ->dehydrated()
                            ->label('Tontinier')
                            ->relationship('tenant', 'name')
                            ->searchable()
                            ->preload()
                            ->prefixIcon('heroicon-o-building-office-2'),
                        Select::make('role')
                            ->label('Rôle')
                            ->options([
                                'owner' => 'Propriétaire',
                                'manager' => 'Gestionnaire',
                                'agent' => 'Agent',
                            ])
                            ->native(false)
                            ->required()
                            ->default('owner'),
                        Select::make('roles')
                            ->label('Rôles Shield')
                            ->relationship('roles', 'name', modifyQueryUsing: fn (Builder $query): Builder => $query->where('guard_name', 'web'))
                            ->multiple()->searchable()->preload()
                            ->visible(fn (Get $get, ?User $record): bool => $record?->tenant_id === null && blank($get('tenant_id')) && $get('role') === 'owner')
                            ->helperText('Rôles et permissions de l’administration. Les administrateurs centraux conservent leur accès complet ; les rôles métier des tontiniers restent séparés.')
                            ->saveRelationshipsUsing(function (User $record, ?array $state): void {
                                Gate::authorize('view-platform-administration');
                                abort_unless($record->isPlatformAdmin(), 403);
                                $ids = array_values(array_unique($state ?? []));
                                $roles = Role::query()->whereIn('id', $ids)->where('guard_name', 'web')->get();
                                abort_unless($roles->count() === count($ids), 422);
                                $record->syncRoles($roles);
                            }),
                        TextInput::make('password')
                            ->label('Mot de passe')
                            ->helperText('Laissez vide pour conserver le mot de passe actuel.')
                            ->prefixIcon('heroicon-o-lock-closed')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state)),
                        Toggle::make('is_active')
                            ->label('Compte actif')
                            ->default(true)
                            ->required(),
                    ])
                    ->columns(2),
                Section::make('Conformité')
                    ->icon('heroicon-o-document-check')
                    ->collapsed()
                    ->components([
                        DateTimePicker::make('terms_accepted_at')
                            ->label('Conditions acceptées le')
                            ->native(false),
                    ]),
            ]);
    }
}
