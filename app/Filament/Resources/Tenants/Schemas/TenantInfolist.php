<?php

namespace App\Filament\Resources\Tenants\Schemas;

use App\Models\Tenant;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TenantInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Vue d’ensemble')
                    ->icon('heroicon-o-building-office-2')
                    ->components([
                        TextEntry::make('name')
                            ->label('Tontinier')
                            ->icon('heroicon-o-building-office')
                            ->weight('semibold'),
                        TextEntry::make('slug')
                            ->label('Identifiant')
                            ->icon('heroicon-o-link')
                            ->copyable()
                            ->copyMessage('Identifiant copié'),
                        TextEntry::make('users_count')
                            ->label('Utilisateurs')
                            ->state(fn (Tenant $record): int => $record->users()->count())
                            ->badge()
                            ->color('info'),
                        TextEntry::make('subscriptions_count')
                            ->label('Abonnements')
                            ->state(fn (Tenant $record): int => $record->subscriptions()->count())
                            ->badge()
                            ->color('gray'),
                    ])
                    ->columns(2),
                Section::make('Statut et historique')
                    ->icon('heroicon-o-clock')
                    ->components([
                        IconEntry::make('is_active')
                            ->label('Compte actif')
                            ->boolean(),
                        TextEntry::make('created_at')
                            ->label('Inscrit le')
                            ->dateTime('d/m/Y à H:i'),
                        TextEntry::make('updated_at')
                            ->label('Dernière modification')
                            ->dateTime('d/m/Y à H:i'),
                        TextEntry::make('deleted_at')
                            ->label('Supprimé le')
                            ->dateTime('d/m/Y à H:i')
                            ->visible(fn (Tenant $record): bool => $record->trashed()),
                    ])
                    ->columns(3),
            ]);
    }
}
