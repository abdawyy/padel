<?php

namespace App\Filament\Resources\Courts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class CourtForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('club_id')
                    ->relationship(
                        'club',
                        'name',
                        modifyQueryUsing: function (Builder $query) {
                            $user = auth()->user();

                            if ($user?->isSuperAdmin()) {
                                return $query;
                            }

                            $clubIds = $user?->accessibleClubIds() ?? [];

                            return empty($clubIds)
                                ? $query->whereRaw('1 = 0')
                                : $query->whereIn('clubs.id', $clubIds);
                        },
                    )
                    ->searchable()
                    ->preload()
                    ->required(),
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
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Select::make('type')
                    ->options(['indoor' => 'Indoor', 'outdoor' => 'Outdoor'])
                    ->required()
                    ->native(false),
                TextInput::make('price_per_hour')
                    ->required()
                    ->numeric()
                    ->prefix('EGP'),
                TextInput::make('capacity')
                    ->required()
                    ->numeric()
                    ->default(4),
                TextInput::make('slot_duration_minutes')
                    ->required()
                    ->numeric()
                    ->default(60)
                    ->suffix('min'),
                Toggle::make('is_active')
                    ->default(true)
                    ->required(),
            ]);
    }
}
