<?php

namespace App\Filament\Resources\PaymentTransactions\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PaymentTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('paymob_transaction_id')->label('Paymob ID')->searchable()->toggleable(),
                TextColumn::make('user.name')->label('User')->searchable()->sortable(),
                TextColumn::make('booking_id')->label('Booking')->sortable(),
                TextColumn::make('amount')->money('EGP')->sortable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'pending' => 'Pending',
                    'success' => 'Success',
                    'failed' => 'Failed',
                    'refunded' => 'Refunded',
                ]),
                Filter::make('booking_id')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('booking_id')->numeric()->label('Booking ID'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        filled($data['booking_id'] ?? null),
                        fn (Builder $q) => $q->where('booking_id', (int) $data['booking_id'])
                    )),
                Filter::make('user_id')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('user_id')->numeric()->label('User ID'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        filled($data['user_id'] ?? null),
                        fn (Builder $q) => $q->where('user_id', (int) $data['user_id'])
                    )),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
