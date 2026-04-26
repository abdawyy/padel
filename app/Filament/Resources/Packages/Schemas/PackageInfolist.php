<?php

namespace App\Filament\Resources\Packages\Schemas;

use App\Models\Package;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PackageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Package Details')
                    ->schema([
                        TextEntry::make('club.name')
                            ->label('Club')
                            ->placeholder('-'),
                        TextEntry::make('name'),
                        TextEntry::make('sport_type')
                            ->badge(),
                        TextEntry::make('type')
                            ->label('Package Type')
                            ->badge()
                            ->formatStateUsing(fn ($state) => [
                                'sessions'  => 'Sessions',
                                'monthly'   => 'Monthly',
                                'quarterly' => 'Quarterly',
                                'yearly'    => 'Yearly',
                                'custom'    => 'Custom',
                            ][$state] ?? $state),
                        TextEntry::make('session_count')
                            ->label('Sessions Included')
                            ->numeric()
                            ->placeholder('-'),
                        TextEntry::make('duration_days')
                            ->label('Duration (days)')
                            ->numeric()
                            ->placeholder('-'),
                        TextEntry::make('price')
                            ->money('EGP'),
                        TextEntry::make('description')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        IconEntry::make('is_active')
                            ->label('Active')
                            ->boolean(),
                        TextEntry::make('deleted_at')
                            ->dateTime()
                            ->visible(fn (Package $record): bool => $record->trashed()),
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->dateTime()
                            ->placeholder('-'),
                    ])
                    ->columns(2),
            ]);
    }
}
