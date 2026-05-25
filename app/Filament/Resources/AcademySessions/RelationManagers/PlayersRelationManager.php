<?php

namespace App\Filament\Resources\AcademySessions\RelationManagers;

use App\Models\User;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PlayersRelationManager extends RelationManager
{
    protected static string $relationship = 'players';

    protected static ?string $title = 'Enrolled Players';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('status')
                ->options([
                    'registered' => 'Registered',
                    'assigned' => 'Assigned',
                    'pending_payment' => 'Pending payment',
                    'cancelled' => 'Cancelled',
                ])
                ->required()
                ->native(false),
            Textarea::make('notes')->rows(2)->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->toggleable(),
                TextColumn::make('skill_level')
                    ->badge()
                    ->formatStateUsing(fn (?int $state): string => $state ? User::skillLevelLabel($state) : '—'),
                TextColumn::make('pivot.status')->label('Status')->badge(),
                TextColumn::make('pivot.notes')->label('Notes')->limit(40)->placeholder('—'),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Select::make('status')
                            ->options([
                                'registered' => 'Registered',
                                'assigned' => 'Assigned',
                            ])
                            ->default('registered')
                            ->required(),
                        Textarea::make('notes'),
                    ])
                    ->mutateFormDataUsing(fn (array $data): array => [
                        'status' => $data['status'] ?? 'registered',
                        'notes' => $data['notes'] ?? null,
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->using(function ($record, array $data, PlayersRelationManager $livewire): void {
                        $livewire->getOwnerRecord()->players()->updateExistingPivot($record->id, [
                            'status' => $data['status'],
                            'notes' => $data['notes'] ?? null,
                        ]);
                    }),
                DetachAction::make()->label('Remove'),
            ]);
    }
}
