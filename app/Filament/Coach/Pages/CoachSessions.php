<?php

namespace App\Filament\Coach\Pages;

use App\Models\AcademySession;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CoachSessions extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?string $navigationLabel = 'My Sessions';

    protected static ?string $title = 'My Sessions';

    protected static ?int $navigationSort = 2;

    public function getView(): string
    {
        return 'filament.coach.pages.coach-sessions';
    }

    public function table(Table $table): Table
    {
        $clubIds = auth()->user()?->clubs()->pluck('clubs.id') ?? collect();

        return $table
            ->query(
                AcademySession::query()
                    ->where('coach_user_id', auth()->id())
                    ->with(['club', 'court'])
                    ->withCount('players')
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
                TextColumn::make('players_count')
                    ->label('Players')
                    ->badge(),
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
                        'scheduled', 'active' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('club_id')
                    ->label('Club')
                    ->options(fn () => \App\Models\Club::query()
                        ->whereIn('id', $clubIds)
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable(),
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('from')->label('From'),
                        DatePicker::make('until')->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('start_time', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('start_time', '<=', $date));
                    }),
                SelectFilter::make('status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->recordActions([
                Action::make('details')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn (AcademySession $record): string => $record->title)
                    ->modalContent(fn (AcademySession $record) => view('filament.coach.partials.session-detail-modal', [
                        'session' => $record->load(['players', 'club', 'court']),
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
            ])
            ->defaultSort('start_time', 'desc');
    }
}
