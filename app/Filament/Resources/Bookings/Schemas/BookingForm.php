<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Illuminate\Database\Eloquent\Builder;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Match Details')
                    ->schema([
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
                        Select::make('owner_user_id')
                            ->relationship(
                                'owner',
                                'name',
                                modifyQueryUsing: function (Builder $query) {
                                    $user = auth()->user();

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
                            ->preload()
                            ->required(),
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
                        DateTimePicker::make('start_time')
                            ->required(),
                        DateTimePicker::make('end_time')
                            ->required(),
                        TextInput::make('total_price')
                            ->required()
                            ->numeric()
                            ->prefix('EGP'),
                        TextInput::make('coach_fee')
                            ->required()
                            ->numeric()
                            ->default(0.0)
                            ->prefix('EGP'),
                        Select::make('match_type')
                            ->options(['private' => 'Private', 'open_match' => 'Open Match'])
                            ->required()
                            ->native(false),
                        Select::make('session_type')
                            ->options([
                                'standard' => 'Standard',
                                'open_match' => 'Open Match',
                                'coached_match' => 'Coached Match',
                                'group_training' => 'Group Training',
                                'private_training' => 'Private Training',
                                'academy_class' => 'Academy Class',
                            ])
                            ->default('standard')
                            ->required()
                            ->native(false),
                        TextInput::make('max_players')
                            ->required()
                            ->numeric()
                            ->default(4),
                        TextInput::make('skill_level'),
                        Select::make('status')
                            ->options(['pending' => 'Pending', 'confirmed' => 'Confirmed', 'cancelled' => 'Cancelled'])
                            ->required()
                            ->native(false),
                        Textarea::make('notes')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Assigned Players')
                    ->description('Owner is added automatically. Use this section to assign additional players to the match.')
                    ->schema([
                        Select::make('participant_ids')
                            ->label('Players')
                            ->multiple()
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search): array {
                                $query = User::query()
                                    ->where('role', 'player')
                                    ->where(function (Builder $query) use ($search) {
                                        $query->where('name', 'like', "%{$search}%")
                                            ->orWhere('email', 'like', "%{$search}%");

                                        if (is_numeric($search)) {
                                            $query->orWhere('id', (int) $search);
                                        }
                                    });

                                $user = auth()->user();

                                if (! $user?->isSuperAdmin()) {
                                    $clubIds = $user?->accessibleClubIds() ?? [];

                                    if (empty($clubIds)) {
                                        return [];
                                    }

                                    $query->whereHas('clubs', fn (Builder $clubQuery) => $clubQuery->whereIn('clubs.id', $clubIds));
                                }

                                return $query
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(fn (User $player) => [$player->id => "#{$player->id} - {$player->name} ({$player->email})"])
                                    ->all();
                            })
                            ->getOptionLabelsUsing(fn (array $values): array => User::query()
                                ->whereIn('id', $values)
                                ->get()
                                ->mapWithKeys(fn (User $player) => [$player->id => "#{$player->id} - {$player->name} ({$player->email})"])
                                ->all())
                            ->helperText('Assign one or more players to this match. The booking owner will also be included automatically.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
