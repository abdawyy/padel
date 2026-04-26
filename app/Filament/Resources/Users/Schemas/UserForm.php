<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->tel()
                    ->maxLength(30),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->columnSpanFull(),
                Select::make('role')
                    ->options(fn () => auth()->user()?->isSuperAdmin()
                        ? [
                            'super_admin' => 'Super Admin',
                            'admin'       => 'Admin',
                            'manager'     => 'Manager',
                            'coach'       => 'Coach',
                            'staff'       => 'Staff',
                            'player'      => 'Player',
                        ]
                        : [
                            'admin'   => 'Admin',
                            'manager' => 'Manager',
                            'coach'   => 'Coach',
                            'staff'   => 'Staff',
                            'player'  => 'Player',
                        ])
                    ->default('player')
                    ->required()
                    ->native(false),
                Toggle::make('is_active')
                    ->default(true)
                    ->required(),
                Select::make('skill_level')
                    ->label('Skill Level (WPT 1–7)')
                    ->options([
                        1 => 'Level 1 – Beginner',
                        2 => 'Level 2 – Beginner+',
                        3 => 'Level 3 – Intermediate',
                        4 => 'Level 4 – Intermediate+',
                        5 => 'Level 5 – Advanced',
                        6 => 'Level 6 – Advanced+',
                        7 => 'Level 7 – Elite / Pro',
                    ])
                    ->native(false)
                    ->nullable(),
                DatePicker::make('date_of_birth')
                    ->label('Date of Birth')
                    ->nullable()
                    ->maxDate(now()->subYears(5)),
                Select::make('preferred_sport')
                    ->label('Preferred Sport')
                    ->options([
                        'padel'      => 'Padel',
                        'tennis'     => 'Tennis',
                        'pickleball' => 'Pickleball',
                        'squash'     => 'Squash',
                    ])
                    ->default('padel')
                    ->native(false),
            ]);
    }
}
