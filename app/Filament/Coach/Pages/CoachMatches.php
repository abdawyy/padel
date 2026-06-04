<?php

namespace App\Filament\Coach\Pages;

use App\Models\Booking;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
        $clubIds = auth()->user()?->clubs()->pluck('clubs.id') ?? collect();

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
                    ->money(config('app.currency', 'EGP')),
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
                SelectFilter::make('club')
                    ->label('Club')
                    ->options(fn () => \App\Models\Club::query()
                        ->whereIn('id', $clubIds)
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'] ?? null,
                        fn (Builder $q, $clubId) => $q->whereHas('court', fn (Builder $court) => $court->where('club_id', $clubId))
                    )),
                Filter::make('date_range')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')->label('From'),
                        \Filament\Forms\Components\DatePicker::make('until')->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('start_time', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('start_time', '<=', $date));
                    }),
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
