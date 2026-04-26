<?php

namespace App\Filament\Resources\AcademySessions\Schemas;

use Illuminate\Support\HtmlString;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AcademySessionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('club.name')
                    ->label('Club')
                    ->placeholder('-'),
                TextEntry::make('court.name')
                    ->label('Court')
                    ->placeholder('-'),
                TextEntry::make('coach.name')
                    ->label('Coach')
                    ->placeholder('-'),
                TextEntry::make('creator.name')
                    ->label('Created By')
                    ->placeholder('-'),
                TextEntry::make('title'),
                TextEntry::make('sport_type'),
                TextEntry::make('session_type')
                    ->label('Package Type'),
                TextEntry::make('skill_level')
                    ->placeholder('-'),
                TextEntry::make('start_time')
                    ->dateTime(),
                TextEntry::make('end_time')
                    ->dateTime(),
                TextEntry::make('max_players')
                    ->numeric(),
                TextEntry::make('price_per_player')
                    ->numeric(),
                TextEntry::make('status'),
                TextEntry::make('players.name')
                    ->label('Assigned Players')
                    ->badge()
                    ->separator(', ')
                    ->placeholder('No players assigned yet')
                    ->columnSpanFull(),
                TextEntry::make('session_plan')
                    ->label('Session Plan')
                    ->placeholder('No session plan added')
                    ->columnSpanFull(),
                TextEntry::make('notes')
                    ->label('Internal Notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('training_video_urls')
                    ->label('Training Videos')
                    ->formatStateUsing(fn (array $state): HtmlString => new HtmlString(
                        collect($state)
                            ->map(fn (string $url) => '<a href="' . e($url) . '" target="_blank" rel="noopener noreferrer">' . e($url) . '</a>')
                            ->implode('<br>')
                    ))
                    ->html()
                    ->placeholder('No videos linked')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
