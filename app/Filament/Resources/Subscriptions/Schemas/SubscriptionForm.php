<?php

namespace App\Filament\Resources\Subscriptions\Schemas;

use App\Models\Subscription;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Abonnement du tontinier')
                    ->description('Offre et état actuel de l’abonnement.')
                    ->icon('heroicon-o-credit-card')
                    ->components([
                        Select::make('tenant_id')
                            ->disabled(fn (?Subscription $record): bool => $record !== null)
                            ->dehydrated()
                            ->label('Tontinier')
                            ->relationship('tenant', 'name')
                            ->searchable()
                            ->preload()
                            ->prefixIcon('heroicon-o-building-office-2')
                            ->required(),
                        Select::make('status')
                            ->label('Statut')
                            ->options([
                                'trialing' => 'Période d’essai',
                                'active' => 'Actif',
                                'past_due' => 'Paiement en retard',
                                'cancelled' => 'Annulé',
                                'expired' => 'Expiré',
                            ])
                            ->native(false)
                            ->required()
                            ->default('trialing'),
                        TextInput::make('amount')
                            ->label('Montant annuel')
                            ->prefixIcon('heroicon-o-banknotes')
                            ->required()
                            ->numeric()
                            ->suffix('GNF')
                            ->default(200000),
                        TextInput::make('currency')
                            ->label('Devise')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->default('GNF'),
                    ])
                    ->columns(2),
                Section::make('Période et échéances')
                    ->description('Dates importantes du cycle de facturation.')
                    ->icon('heroicon-o-calendar-days')
                    ->components([
                        DateTimePicker::make('trial_ends_at')
                            ->label('Fin de la période d’essai')
                            ->native(false),
                        DateTimePicker::make('starts_at')
                            ->label('Début de l’abonnement')
                            ->native(false),
                        DateTimePicker::make('ends_at')
                            ->label('Fin de l’abonnement')
                            ->native(false),
                    ])
                    ->columns(3),
            ]);
    }
}
