<?php

namespace App\Filament\Saas\Resources\Users;

use App\Filament\Saas\Resources\Users\Pages\ListSaasUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SaasUserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = 'All Users';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'Academies';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('phone')->placeholder('—'),
                TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'super_admin' => 'danger',
                        'admin'       => 'warning',
                        'manager'     => 'info',
                        'coach'       => 'success',
                        'player'      => 'gray',
                        default       => 'gray',
                    }),
                TextColumn::make('clubs_count')
                    ->counts('clubs')
                    ->label('Academies'),
                IconColumn::make('is_active')->boolean()->label('Active'),
                TextColumn::make('created_at')->date()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('role')
                    ->options([
                        'super_admin' => 'Super Admin',
                        'admin'       => 'Admin',
                        'manager'     => 'Manager',
                        'coach'       => 'Coach',
                        'staff'       => 'Staff',
                        'player'      => 'Player',
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSaasUsers::route('/'),
        ];
    }
}
