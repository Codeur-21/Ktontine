<?php

namespace App\Filament\Resources\Tenants\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TenantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Tontinier')
                    ->description(fn ($record): string => $record->slug)
                    ->icon('heroicon-o-building-office-2')
                    ->weight('semibold')
                    ->searchable(['name', 'slug'])
                    ->sortable(),
                TextColumn::make('users_count')
                    ->label('Utilisateurs')
                    ->counts('users')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('subscriptions_count')
                    ->label('Abonnements')
                    ->counts('subscriptions')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Inscrit le')
                    ->dateTime('d/m/Y à H:i')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Dernière activité')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('État du compte')
                    ->trueLabel('Tontiniers actifs')
                    ->falseLabel('Tontiniers suspendus')
                    ->placeholder('Tous les tontiniers'),
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
            ->emptyStateIcon('heroicon-o-building-office-2')
            ->emptyStateHeading('Aucun tontinier')
            ->emptyStateDescription('Les nouveaux tontiniers apparaîtront ici après leur inscription.');
    }
}
