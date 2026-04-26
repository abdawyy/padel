<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextEntry::make('name'),
                TextEntry::make('email')
                    ->label('Email address'),
                TextEntry::make('phone')
                    ->placeholder('-'),
                TextEntry::make('role')
                    ->badge(),
                TextEntry::make('skill_level')
                    ->label('Skill Level')
                    ->badge()
                    ->formatStateUsing(fn (?int $state): string => $state ? User::skillLevelLabel($state) : '-')
                    ->color(fn (?int $state): string => match (true) {
                        $state === null     => 'gray',
                        $state <= 2         => 'info',
                        $state <= 4         => 'warning',
                        $state <= 6         => 'success',
                        default             => 'danger',
                    })
                    ->placeholder('-'),
                TextEntry::make('date_of_birth')
                    ->label('Date of Birth')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('preferred_sport')
                    ->label('Preferred Sport')
                    ->badge()
                    ->placeholder('-'),
                TextEntry::make('email_verified_at')
                    ->dateTime()
                    ->placeholder('-'),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (User $record): bool => $record->trashed()),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
