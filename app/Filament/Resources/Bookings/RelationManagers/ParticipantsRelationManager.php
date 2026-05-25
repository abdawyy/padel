<?php

namespace App\Filament\Resources\Bookings\RelationManagers;

use App\Models\User;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ParticipantsRelationManager extends RelationManager
{
    protected static string $relationship = 'participants';

    protected static ?string $title = 'Participants & Payments';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('payment_status')
                ->options([
                    'pending' => 'Pending',
                    'paid' => 'Paid',
                    'refunded' => 'Refunded',
                ])
                ->required()
                ->native(false),
            TextInput::make('amount_due')
                ->label('Amount due (EGP)')
                ->numeric()
                ->minValue(0)
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->toggleable(),
                TextColumn::make('pivot.amount_due')->label('Amount due')->money('EGP'),
                TextColumn::make('pivot.payment_status')
                    ->label('Payment')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'refunded' => 'info',
                        default => 'warning',
                    }),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name', 'email'])
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        TextInput::make('amount_due')->numeric()->minValue(0)->default(0)->required(),
                        Select::make('payment_status')
                            ->options(['pending' => 'Pending', 'paid' => 'Paid'])
                            ->default('pending')
                            ->required(),
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        return [
                            'amount_due' => $data['amount_due'] ?? 0,
                            'payment_status' => $data['payment_status'] ?? 'pending',
                        ];
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->using(function ($record, array $data, ParticipantsRelationManager $livewire): void {
                        $livewire->getOwnerRecord()->participants()->updateExistingPivot($record->id, [
                            'amount_due' => $data['amount_due'],
                            'payment_status' => $data['payment_status'],
                        ]);
                    }),
                DetachAction::make(),
            ]);
    }
}
