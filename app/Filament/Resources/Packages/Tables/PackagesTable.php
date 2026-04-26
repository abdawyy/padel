<?php

namespace App\Filament\Resources\Packages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PackagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('club.name')
                    ->label('Club')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sport_type')
                    ->badge()
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Package Type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => [
                        'sessions'  => 'Sessions',
                        'monthly'   => 'Monthly',
                        'quarterly' => 'Quarterly',
                        'yearly'    => 'Yearly',
                        'custom'    => 'Custom',
                    ][$state] ?? $state)
                    ->sortable(),
                TextColumn::make('session_count')
                    ->label('Sessions')
                    ->numeric()
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('duration_days')
                    ->label('Days')
                    ->numeric()
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('price')
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('subscriptions_count')
                    ->counts('subscriptions')
                    ->label('Subscribers')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
