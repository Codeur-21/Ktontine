<?php

namespace App\Filament\Resources\Subscriptions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SubscriptionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Résumé de l’abonnement')
                    ->icon('heroicon-o-credit-card')
                    ->components([
                        TextEntry::make('tenant.name')
                            ->label('Tontinier')
                            ->icon('heroicon-o-building-office-2')
                            ->weight('semibold'),
                        TextEntry::make('status')
                            ->label('Statut')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'trialing' => 'Période d’essai',
                                'active' => 'Actif',
                                'past_due' => 'Paiement en retard',
                                'cancelled' => 'Annulé',
                                'expired' => 'Expiré',
                                default => ucfirst($state),
                            })
                            ->color(fn (string $state): string => match ($state) {
                                'trialing' => 'info',
                                'active' => 'success',
                                'past_due' => 'danger',
                                'cancelled' => 'gray',
                                'expired' => 'warning',
                                default => 'gray',
                            }),
                        TextEntry::make('amount')
                            ->label('Montant annuel')
                            ->money('GNF', locale: 'fr')
                            ->icon('heroicon-o-banknotes')
                            ->weight('semibold'),
                        TextEntry::make('currency')
                            ->label('Devise')
                            ->badge()
                            ->color('gray'),
                    ])
                    ->columns(2),
                Section::make('Calendrier')
                    ->icon('heroicon-o-calendar-days')
                    ->components([
                        TextEntry::make('trial_ends_at')->label('Fin de l’essai')->dateTime('d/m/Y à H:i')->placeholder('—'),
                        TextEntry::make('starts_at')->label('Début')->dateTime('d/m/Y à H:i')->placeholder('—'),
                        TextEntry::make('ends_at')->label('Échéance')->dateTime('d/m/Y à H:i')->placeholder('Sans échéance'),
                    ])
                    ->columns(3),
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
