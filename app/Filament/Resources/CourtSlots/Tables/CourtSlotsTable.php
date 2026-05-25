<?php

namespace App\Filament\Resources\CourtSlots\Tables;

use App\Models\CourtSlot;
use App\Services\CourtSlotSchedulingService;
use Carbon\Carbon;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Support\Collection;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CourtSlotsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('court.name')
                    ->label('Court')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slot_type')
                    ->badge()
                    ->searchable(),
                TextColumn::make('day_of_week')
                    ->formatStateUsing(fn ($state) => [0 => 'Sun', 1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat'][$state] ?? $state)
                    ->sortable(),
                TextColumn::make('start_time')
                    ->time()
                    ->sortable(),
                TextColumn::make('end_time')
                    ->time()
                    ->sortable(),
                TextColumn::make('coach.name')
                    ->label('Coach')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('max_players')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('price')
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('skill_level')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
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
                SelectFilter::make('court_id')
                    ->relationship('court', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('slot_type')
                    ->options([
                        'open_match' => 'Open match',
                        'coached_match' => 'Coached match',
                        'training' => 'Training',
                        'academy_class' => 'Academy class',
                        'private_training' => 'Private training',
                    ]),
                SelectFilter::make('is_active')
                    ->options(['1' => 'Active', '0' => 'Inactive'])
                    ->query(fn ($query, array $data) => $query->when(
                        isset($data['value']) && $data['value'] !== '',
                        fn ($q) => $q->where('is_active', (bool) $data['value'])
                    )),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('scheduleAcademySessions')
                        ->label('Generate academy sessions')
                        ->icon('heroicon-o-calendar-days')
                        ->form([
                            DatePicker::make('date')->required()->native(false),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $date = Carbon::parse($data['date']);
                            $service = app(CourtSlotSchedulingService::class);
                            $created = 0;
                            $failed = 0;

                            foreach ($records as $courtSlot) {
                                /** @var CourtSlot $courtSlot */
                                $courtSlot->loadMissing('court.club');

                                if (! $courtSlot->court?->club) {
                                    $failed++;
                                    continue;
                                }

                                try {
                                    $service->schedule(
                                        $courtSlot,
                                        $courtSlot->court->club,
                                        auth()->user(),
                                        $date,
                                    );
                                    $created++;
                                } catch (\Throwable) {
                                    $failed++;
                                }
                            }

                            Notification::make()
                                ->title("Scheduled {$created} session(s)")
                                ->body($failed > 0 ? "{$failed} slot(s) could not be scheduled (day mismatch or conflict)." : null)
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
