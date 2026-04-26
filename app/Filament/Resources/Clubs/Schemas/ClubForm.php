<?php

namespace App\Filament\Resources\Clubs\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ClubForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Select::make('sport_type')
                    ->options([
                        'padel' => 'Padel',
                        'tennis' => 'Tennis',
                        'pickleball' => 'Pickleball',
                        'squash' => 'Squash',
                        'multi_sport' => 'Multi Sport',
                    ])
                    ->default('padel')
                    ->required()
                    ->native(false),
                Textarea::make('address')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),
                Select::make('subscription_status')
                    ->options([
                        'active' => 'Active',
                        'trial' => 'Trial',
                        'paused' => 'Paused',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('active')
                    ->required()
                    ->native(false),
                KeyValue::make('settings')
                    ->keyLabel('Setting')
                    ->valueLabel('Value')
                    ->columnSpanFull(),
            ]);
    }
}
