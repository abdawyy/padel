<?php

namespace App\Filament\Resources\AcademySessions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AcademySessionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('club.name')
                    ->label('Club')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('court.name')
                    ->label('Court')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('coach.name')
                    ->label('Coach')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('session_type')
                    ->label('Package Type')
                    ->badge()
                    ->searchable(),
                TextColumn::make('start_time')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('end_time')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('players_count')
                    ->counts('players')
                    ->label('Players'),
                TextColumn::make('max_players')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('price_per_player')
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
