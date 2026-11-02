<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Profil utilisateur')
                    ->icon('heroicon-o-user-circle')
                    ->components([
                        TextEntry::make('name')
                            ->label('Nom complet')
                            ->icon('heroicon-o-user')
                            ->weight('semibold'),
                        TextEntry::make('email')
                            ->label('Adresse e-mail')
                            ->icon('heroicon-o-envelope')
                            ->copyable(),
                        TextEntry::make('phone')
                            ->label('Téléphone')
                            ->icon('heroicon-o-phone')
                            ->copyable(),
                        TextEntry::make('tenant.name')
                            ->label('Tontinier')
                            ->icon('heroicon-o-building-office-2')
                            ->placeholder('Aucun espace'),
                    ])
                    ->columns(2),
                Section::make('Accès au compte')
                    ->icon('heroicon-o-shield-check')
                    ->components([
                        TextEntry::make('role')
                            ->label('Rôle')
                            ->getStateUsing(fn (User $record): array|string => $record->tenant_id === null
                                ? ($record->roles->isEmpty() ? ['Aucun rôle Shield'] : $record->roles->pluck('name')->all())
                                : $record->role)
                            ->badge()
                            ->formatStateUsing(fn (string $state, User $record): string => $record->tenant_id === null ? $state : match ($state) {
                                'owner' => 'Propriétaire',
                                'manager' => 'Gestionnaire',
                                'agent' => 'Agent',
                                default => ucfirst($state),
                            })
                            ->color(fn (string $state): string => match ($state) {
                                'owner' => 'warning',
                                'manager' => 'info',
                                default => 'gray',
                            }),
                        IconEntry::make('is_active')
                            ->label('Compte actif')
                            ->boolean(),
                        TextEntry::make('email_verified_at')
                            ->label('E-mail vérifié le')
                            ->dateTime('d/m/Y à H:i')
                            ->placeholder('Non vérifié'),
                        TextEntry::make('terms_accepted_at')
                            ->label('Conditions acceptées le')
                            ->dateTime('d/m/Y à H:i')
                            ->placeholder('Non renseigné'),
                    ])
                    ->columns(2),
                Section::make('Historique')
                    ->icon('heroicon-o-clock')
                    ->components([
                        TextEntry::make('created_at')->label('Créé le')->dateTime('d/m/Y à H:i'),
                        TextEntry::make('updated_at')->label('Modifié le')->dateTime('d/m/Y à H:i'),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}
