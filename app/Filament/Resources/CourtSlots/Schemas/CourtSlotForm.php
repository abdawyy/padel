<?php

namespace App\Filament\Resources\CourtSlots\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class CourtSlotForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('court_id')
                    ->relationship(
                        'court',
                        'name',
                        modifyQueryUsing: function (Builder $query) {
                            $user = auth()->user();

                            if ($user?->isSuperAdmin()) {
                                return $query;
                            }

                            $clubIds = $user?->accessibleClubIds() ?? [];

                            return empty($clubIds)
                                ? $query->whereRaw('1 = 0')
                                : $query->whereIn('club_id', $clubIds);
                        },
                    )
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Select::make('sport_type')
                    ->options([
                        'padel' => 'Padel',
                        'tennis' => 'Tennis',
                        'pickleball' => 'Pickleball',
                        'squash' => 'Squash',
                    ])
                    ->default('padel')
                    ->required()
                    ->native(false),
                Select::make('slot_type')
                    ->options([
                        'open_match' => 'Open Match',
                        'coached_match' => 'Coached Match',
                        'training' => 'Training',
                        'academy_class' => 'Academy Class',
                        'private_training' => 'Private Training',
                    ])
                    ->default('training')
                    ->required()
                    ->native(false),
                Select::make('day_of_week')
                    ->options([
                        0 => 'Sunday',
                        1 => 'Monday',
                        2 => 'Tuesday',
                        3 => 'Wednesday',
                        4 => 'Thursday',
                        5 => 'Friday',
                        6 => 'Saturday',
                    ])
                    ->required()
                    ->native(false),
                Select::make('coach_user_id')
                    ->relationship(
                        'coach',
                        'name',
                        modifyQueryUsing: function (Builder $query) {
                            $user = auth()->user();

                            $query->whereIn('role', ['admin', 'manager', 'coach', 'staff']);

                            if ($user?->isSuperAdmin()) {
                                return $query;
                            }

                            $clubIds = $user?->accessibleClubIds() ?? [];

                            return empty($clubIds)
                                ? $query->whereRaw('1 = 0')
                                : $query->whereHas('clubs', fn (Builder $clubQuery) => $clubQuery->whereIn('clubs.id', $clubIds));
                        },
                    )
                    ->searchable()
                    ->preload(),
                TimePicker::make('start_time')
                    ->required()
                    ->seconds(false),
                TimePicker::make('end_time')
                    ->required()
                    ->seconds(false),
                TextInput::make('max_players')
                    ->required()
                    ->numeric()
                    ->default(4),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->prefix('EGP'),
                TextInput::make('skill_level'),
                Toggle::make('is_active')
                    ->default(true)
                    ->required(),
            ]);
    }
}
