<?php

namespace App\Filament\Resources\Tenants\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identité du tontinier')
                    ->description('Informations utilisées pour identifier cet espace dans la plateforme.')
                    ->icon('heroicon-o-building-office-2')
                    ->components([
                        TextInput::make('name')
                            ->label('Nom du tontinier')
                            ->placeholder('Ex. Établissements Diallo')
                            ->prefixIcon('heroicon-o-building-office')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->label('Identifiant unique')
                            ->helperText('Utilisé dans les URLs et les références internes.')
                            ->prefixIcon('heroicon-o-link')
                            ->required()
                            ->unique(ignoreRecord: true),
                    ])
                    ->columns(2),
                Section::make('État du compte')
                    ->description('Un compte suspendu ne doit plus accéder à son espace.')
                    ->icon('heroicon-o-shield-check')
                    ->components([
                        Toggle::make('is_active')
                            ->label('Compte actif')
                            ->helperText('Désactivez cette option pour suspendre temporairement le tontinier.')
                            ->default(true)
                            ->required(),
                    ]),
            ])
            ->columns(1);
    }
}
