<?php

namespace App\Filament\Coach\Pages;

use App\Models\Booking;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CoachMatches extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTrophy;

    protected static ?string $navigationLabel = 'My Matches';

    protected static ?string $title = 'Coached Matches';

    protected static ?int $navigationSort = 3;

    public function getView(): string
    {
        return 'filament.coach.pages.coach-matches';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Booking::query()
                    ->where('coach_user_id', auth()->id())
                    ->with(['court.club', 'owner'])
            )
            ->columns([
                TextColumn::make('court.club.name')
                    ->label('Club')
                    ->sortable(),
                TextColumn::make('court.name')
                    ->label('Court')
                    ->sortable(),
                TextColumn::make('owner.name')
                    ->label('Player')
                    ->sortable(),
                TextColumn::make('match_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state))),
                TextColumn::make('skill_level')
                    ->label('Level')
                    ->badge()
                    ->placeholder('Any'),
                TextColumn::make('start_time')
                    ->label('Start')
                    ->dateTime('D, d M Y H:i')
                    ->sortable(),
                TextColumn::make('coach_fee')
                    ->label('Coach Fee')
                    ->money('USD'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'confirmed' => 'success',
                        'pending' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->defaultSort('start_time', 'desc');
    }
}
