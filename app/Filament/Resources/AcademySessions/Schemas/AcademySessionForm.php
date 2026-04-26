<?php

namespace App\Filament\Resources\AcademySessions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Illuminate\Database\Eloquent\Builder;

class AcademySessionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Package Details')
                    ->schema([
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
                        Select::make('created_by_user_id')
                            ->relationship('creator', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn () => auth()->id())
                            ->disabled()
                            ->dehydrated()
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
                        Select::make('session_type')
                            ->label('Package Type')
                            ->options([
                                'group_training' => 'Group Training',
                                'private_training' => 'Private Training',
                                'academy_class' => 'Academy Class',
                                'open_match' => 'Open Match',
                                'coached_match' => 'Coached Match',
                            ])
                            ->default('group_training')
                            ->required()
                            ->native(false),
                        TextInput::make('skill_level'),
                        DateTimePicker::make('start_time')
                            ->required(),
                        DateTimePicker::make('end_time')
                            ->required(),
                        TextInput::make('max_players')
                            ->required()
                            ->numeric()
                            ->default(4),
                        TextInput::make('price_per_player')
                            ->required()
                            ->numeric()
                            ->default(0.0)
                            ->prefix('EGP'),
                        Select::make('status')
                            ->options([
                                'scheduled' => 'Scheduled',
                                'active' => 'Active',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('scheduled')
                            ->required()
                            ->native(false),
                    ])
                    ->columns(2),
                Section::make('Session Content')
                    ->description('Visible to players in their training session details.')
                    ->schema([
                        Textarea::make('session_plan')
                            ->label('Session Plan')
                            ->rows(6)
                            ->placeholder("Warm-up\nTechnique focus\nMatch scenarios\nCool down")
                            ->helperText('This is the player-facing agenda for the session.')
                            ->columnSpanFull(),
                        Textarea::make('video_urls')
                            ->label('Training Videos')
                            ->rows(5)
                            ->placeholder("https://www.youtube.com/watch?v=...\nhttps://youtu.be/...\nhttps://www.youtube.com/shorts/...")
                            ->helperText('Add one YouTube URL per line. The first video is used as the main preview for players.')
                            ->afterStateHydrated(function (Textarea $component, $state, $record): void {
                                $videos = collect(is_array($state) ? $state : [])
                                    ->prepend($record?->video_url)
                                    ->filter(fn ($url) => is_string($url) && filled(trim($url)))
                                    ->map(fn (string $url) => trim($url))
                                    ->unique()
                                    ->implode(PHP_EOL);

                                $component->state($videos);
                            })
                            ->dehydrateStateUsing(fn (?string $state): array => collect(preg_split('/\r\n|\r|\n/', $state ?? ''))
                                ->map(fn ($url) => trim((string) $url))
                                ->filter()
                                ->unique()
                                ->values()
                                ->all())
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->label('Internal Notes')
                            ->helperText('Optional admin/coach notes not intended to replace the session plan.')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
                Section::make('Assigned Players')
                    ->description('Assign multiple players to this academy package or session.')
                    ->schema([
                        Select::make('players')
                            ->relationship(
                                'players',
                                'name',
                                modifyQueryUsing: fn (Builder $query) => $query->where('role', 'player'),
                            )
                            ->multiple()
                            ->searchable(['id', 'name', 'email'])
                            ->getOptionLabelFromRecordUsing(fn ($record) => "#{$record->id} - {$record->name} ({$record->email})")
                            ->helperText('Search by player ID, name, or email to assign this academy package/session.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
