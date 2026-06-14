<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Filament\Resources\PaymentTransactions\PaymentTransactionResource;
use App\Models\Booking;
use App\Support\Money;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class BookingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('court.name')
                    ->label('Court')
                    ->placeholder('-'),
                TextEntry::make('sport_type'),
                TextEntry::make('owner.name')
                    ->label('Owner')
                    ->placeholder('-'),
                TextEntry::make('coach.name')
                    ->label('Coach')
                    ->placeholder('-'),
                TextEntry::make('start_time')
                    ->dateTime(),
                TextEntry::make('end_time')
                    ->dateTime(),
                TextEntry::make('total_price')
                    ->money(),
                TextEntry::make('coach_fee')
                    ->numeric(),
                TextEntry::make('match_type')
                    ->badge(),
                TextEntry::make('session_type'),
                TextEntry::make('max_players')
                    ->numeric(),
                TextEntry::make('skill_level')
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('payment_transactions_link')
                    ->label('Payment transactions')
                    ->state(fn (Booking $record): string => (string) $record->paymentTransactions()->count().' transaction(s)')
                    ->url(fn (Booking $record): string => PaymentTransactionResource::getUrl('index', [
                        'tableFilters' => ['booking_id' => ['value' => $record->id]],
                    ])),
                TextEntry::make('participants_payment_summary')
                    ->label('Participants & payment status')
                    ->state(function (Booking $record): string {
                        $record->loadMissing('participants');

                        if ($record->participants->isEmpty()) {
                            return 'No players assigned yet';
                        }

                        return $record->participants
                            ->map(function ($participant): string {
                                $status = ucfirst((string) ($participant->pivot->payment_status ?? 'pending'));
                                $amount = Money::format($participant->pivot->amount_due ?? 0);

                                return "{$participant->name}: {$status} ({$amount})";
                            })
                            ->join("\n");
                    })
                    ->listWithLineBreaks()
                    ->columnSpanFull(),
                TextEntry::make('notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Booking $record): bool => $record->trashed()),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
