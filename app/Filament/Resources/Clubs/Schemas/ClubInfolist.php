<?php

namespace App\Filament\Resources\Clubs\Schemas;

use App\Models\Club;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ClubInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('sport_type'),
                TextEntry::make('address')
                    ->columnSpanFull(),
                TextEntry::make('subscription_status'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Club $record): bool => $record->trashed()),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
