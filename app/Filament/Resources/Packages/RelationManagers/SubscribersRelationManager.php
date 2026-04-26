<?php

namespace App\Filament\Resources\Packages\RelationManagers;

use App\Models\User;
use Filament\Actions\AttachAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubscribersRelationManager extends RelationManager
{
    protected static string $relationship = 'subscribers';

    protected static ?string $title = 'Assigned Players';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Player')
                    ->options(fn (): array => User::query()
                        ->where('role', 'player')
                        ->orderBy('name')
                        ->get()
                        ->mapWithKeys(fn (User $user) => [
                            $user->id => "#{$user->id} - {$user->name} ({$user->email})",
                        ])
                        ->all())
                    ->searchable()
                    ->preload()
                    ->visibleOn('create')
                    ->required(),
                DatePicker::make('starts_at')
                    ->label('Start Date')
                    ->required()
                    ->default(now()),
                DatePicker::make('expires_at')
                    ->label('Expiry Date')
                    ->required()
                    ->after('starts_at'),
                TextInput::make('sessions_remaining')
                    ->label('Sessions Remaining')
                    ->numeric()
                    ->minValue(0)
                    ->placeholder('Leave blank for unlimited'),
                Select::make('status')
                    ->options([
                        'active'    => 'Active',
                        'expired'   => 'Expired',
                        'suspended' => 'Suspended',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('active')
                    ->required()
                    ->native(false),
                Textarea::make('notes')
                    ->columnSpanFull()
                    ->placeholder('Optional notes about this subscription'),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Player')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('skill_level')
                    ->label('Level')
                    ->badge()
                    ->formatStateUsing(fn (?int $state): string => $state ? User::skillLevelLabel($state) : '—')
                    ->color(fn (?int $state): string => match (true) {
                        $state === null => 'gray',
                        $state <= 2     => 'info',
                        $state <= 4     => 'warning',
                        $state <= 6     => 'success',
                        default         => 'danger',
                    })
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('pivot.starts_at')
                    ->label('Start Date')
                    ->date()
                    ->sortable(),
                TextColumn::make('pivot.expires_at')
                    ->label('Expiry Date')
                    ->date()
                    ->sortable()
                    ->color(fn ($record) => $record->pivot->expires_at < now()->toDateString() ? 'danger' : 'success'),
                TextColumn::make('pivot.sessions_remaining')
                    ->label('Sessions Left')
                    ->numeric()
                    ->placeholder('∞')
                    ->sortable(),
                TextColumn::make('pivot.status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'    => 'success',
                        'expired'   => 'danger',
                        'suspended' => 'warning',
                        'cancelled' => 'gray',
                        default     => 'gray',
                    }),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Assign Player')
                    ->using(function (array $data, SubscribersRelationManager $livewire): void {
                        $livewire->getOwnerRecord()->subscribers()->attach($data['user_id'], [
                            'starts_at'          => $data['starts_at'],
                            'expires_at'         => $data['expires_at'],
                            'sessions_remaining' => $data['sessions_remaining'] ?? null,
                            'status'             => $data['status'] ?? 'active',
                            'notes'              => $data['notes'] ?? null,
                        ]);
                    })
                    ->createAnother(false),
            ])
            ->recordActions([
                EditAction::make()
                    ->using(function ($record, array $data, SubscribersRelationManager $livewire): void {
                        $livewire->getOwnerRecord()->subscribers()->updateExistingPivot($record->id, [
                            'starts_at'          => $data['starts_at'],
                            'expires_at'         => $data['expires_at'],
                            'sessions_remaining' => $data['sessions_remaining'] ?? null,
                            'status'             => $data['status'] ?? 'active',
                            'notes'              => $data['notes'] ?? null,
                        ]);
                    }),
                DetachAction::make()
                    ->label('Remove'),
            ])
            ->toolbarActions([
                DetachBulkAction::make()
                    ->label('Remove Selected'),
            ]);
    }
}
