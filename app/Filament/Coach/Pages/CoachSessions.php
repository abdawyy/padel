<?php

namespace App\Filament\Coach\Pages;

use App\Models\AcademySession;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CoachSessions extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?string $navigationLabel = 'My Training';

    protected static ?string $title = 'My Training Sessions';

    protected static ?int $navigationSort = 2;

    public function getView(): string
    {
        return 'filament.coach.pages.coach-sessions';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AcademySession::query()
                    ->where('coach_user_id', auth()->id())
                    ->with(['club', 'court'])
            )
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('club.name')
                    ->label('Club')
                    ->sortable(),
                TextColumn::make('court.name')
                    ->label('Court')
                    ->sortable(),
                TextColumn::make('session_type')
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
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled' => 'info',
                        'ongoing' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'ongoing' => 'Ongoing',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->defaultSort('start_time', 'desc');
    }
}
