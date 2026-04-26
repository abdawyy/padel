<?php

namespace App\Filament\Resources\Packages\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class PackageForm
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
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Select::make('sport_type')
                            ->options([
                                'padel'      => 'Padel',
                                'tennis'     => 'Tennis',
                                'pickleball' => 'Pickleball',
                                'squash'     => 'Squash',
                            ])
                            ->default('padel')
                            ->required()
                            ->native(false),
                        Select::make('type')
                            ->label('Package Type')
                            ->options([
                                'sessions'  => 'Sessions (fixed count)',
                                'monthly'   => 'Monthly',
                                'quarterly' => 'Quarterly (3 months)',
                                'yearly'    => 'Yearly',
                                'custom'    => 'Custom duration',
                            ])
                            ->required()
                            ->native(false)
                            ->live(),
                        TextInput::make('session_count')
                            ->label('Number of Sessions')
                            ->numeric()
                            ->minValue(1)
                            ->visible(fn ($get) => $get('type') === 'sessions')
                            ->required(fn ($get) => $get('type') === 'sessions'),
                        TextInput::make('duration_days')
                            ->label('Duration (days)')
                            ->numeric()
                            ->minValue(1)
                            ->visible(fn ($get) => $get('type') === 'custom')
                            ->helperText('Leave blank for monthly/quarterly/yearly; they are calculated automatically.'),
                        TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('EGP'),
                        Textarea::make('description')
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }
}
