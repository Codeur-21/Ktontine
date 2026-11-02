<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Pages\Communication;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('roles'))
            ->columns([
                TextColumn::make('name')
                    ->label('Utilisateur')
                    ->description(fn ($record): string => $record->email)
                    ->icon('heroicon-o-user-circle')
                    ->weight('semibold')
                    ->searchable(['name', 'email'])
                    ->sortable(),
                TextColumn::make('tenant.name')
                    ->label('Tontinier')
                    ->icon('heroicon-o-building-office-2')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Aucun espace'),
                TextColumn::make('phone')
                    ->label('Téléphone')
                    ->icon('heroicon-o-phone')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Numéro copié'),
                TextColumn::make('role')
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
                        'agent' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y à H:i')
                    ->sortable(),
                TextColumn::make('email_verified_at')
                    ->label('E-mail vérifié')
                    ->dateTime('d/m/Y à H:i')
                    ->placeholder('Non vérifié')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Rôle dans l’espace tontinier')
                    ->options([
                        'owner' => 'Propriétaire',
                        'manager' => 'Gestionnaire',
                        'agent' => 'Agent',
                    ]),
                SelectFilter::make('tenant_id')
                    ->label('Tontinier')
                    ->relationship('tenant', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_active')
                    ->label('État du compte')
                    ->trueLabel('Utilisateurs actifs')
                    ->falseLabel('Utilisateurs suspendus')
                    ->placeholder('Tous les utilisateurs'),
            ])
            ->recordActions([
                Action::make('writeToTenant')->label('Écrire au tontinier')->icon('heroicon-o-envelope')->iconButton()->tooltip('Écrire au tontinier')
                    ->visible(fn (User $record): bool => $record->tenant_id !== null && $record->role === 'owner')
                    ->url(fn (User $record): string => Communication::getUrl(['recipient' => $record->id])),
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
            ->emptyStateIcon('heroicon-o-users')
            ->emptyStateHeading('Aucun utilisateur')
            ->emptyStateDescription('Ajoutez un utilisateur ou attendez la prochaine inscription.');
    }
}
