<?php

namespace App\Filament\Resources\CourtSlots\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CourtSlotInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('court.name')
                    ->label('Court')
                    ->placeholder('-'),
                TextEntry::make('title'),
                TextEntry::make('sport_type'),
                TextEntry::make('slot_type'),
                TextEntry::make('day_of_week')
                    ->formatStateUsing(fn ($state) => [0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'][$state] ?? $state),
                TextEntry::make('start_time')
                    ->time(),
                TextEntry::make('end_time')
                    ->time(),
                TextEntry::make('coach.name')
                    ->label('Coach')
                    ->placeholder('-'),
                TextEntry::make('max_players')
                    ->numeric(),
                TextEntry::make('price')
                    ->money(),
                TextEntry::make('skill_level')
                    ->placeholder('-'),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
