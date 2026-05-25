<?php

namespace App\Filament\Resources\PaymentTransactions\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\PaymentTransactions\PaymentTransactionResource;
use App\Models\PaymentTransaction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewPaymentTransaction extends ViewRecord
{
    protected static string $resource = PaymentTransactionResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('paymob_transaction_id')->label('Paymob transaction'),
            TextEntry::make('user.name')->label('User'),
            TextEntry::make('amount')->money('EGP'),
            TextEntry::make('status')->badge(),
            TextEntry::make('booking_id')
                ->label('Booking')
                ->url(fn (PaymentTransaction $record): ?string => $record->booking_id
                    ? BookingResource::getUrl('view', ['record' => $record->booking_id])
                    : null),
            TextEntry::make('academy_session_id')->label('Academy session'),
            TextEntry::make('provider_payload')
                ->formatStateUsing(fn ($state) => json_encode($state, JSON_PRETTY_PRINT))
                ->columnSpanFull(),
            TextEntry::make('created_at')->dateTime(),
        ]);
    }
}
