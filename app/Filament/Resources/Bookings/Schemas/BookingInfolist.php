<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Models\Booking;
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
                TextEntry::make('participants.name')
                    ->label('Assigned Players')
                    ->badge()
                    ->separator(', ')
                    ->placeholder('No players assigned yet')
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
