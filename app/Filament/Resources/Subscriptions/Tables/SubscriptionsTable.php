<?php

namespace App\Filament\Resources\Subscriptions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SubscriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tenant.name')
                    ->label('Tontinier')
                    ->icon('heroicon-o-building-office-2')
                    ->weight('semibold')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
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
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'trialing' => 'heroicon-o-clock',
                        'active' => 'heroicon-o-check-circle',
                        'past_due' => 'heroicon-o-exclamation-triangle',
                        'cancelled' => 'heroicon-o-x-circle',
                        'expired' => 'heroicon-o-calendar-days',
                        default => 'heroicon-o-minus-circle',
                    })
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Montant annuel')
                    ->money('GNF', locale: 'fr')
                    ->weight('semibold')
                    ->sortable(),
                TextColumn::make('trial_ends_at')
                    ->label('Fin de l’essai')
                    ->date('d/m/Y')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->label('Début')
                    ->date('d/m/Y')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->label('Échéance')
                    ->date('d/m/Y')
                    ->placeholder('Sans échéance')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Mis à jour')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->multiple()
                    ->options([
                        'trialing' => 'Période d’essai',
                        'active' => 'Actif',
                        'past_due' => 'Paiement en retard',
                        'cancelled' => 'Annulé',
                        'expired' => 'Expiré',
                    ]),
                SelectFilter::make('tenant_id')
                    ->label('Tontinier')
                    ->relationship('tenant', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Consulter')
                    ->iconButton()
                    ->tooltip('Consulter'),
                EditAction::make()
                    ->label('Modifier')
                    ->iconButton()
                    ->tooltip('Modifier'),
                DeleteAction::make()
                    ->label('Supprimer')
                    ->iconButton()
                    ->tooltip('Supprimer'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Supprimer la sélection'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->emptyStateIcon('heroicon-o-credit-card')
            ->emptyStateHeading('Aucun abonnement')
            ->emptyStateDescription('Les abonnements des tontiniers seront suivis depuis cette page.');
    }
}
