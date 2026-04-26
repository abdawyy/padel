<?php

namespace App\Filament\Resources\Clubs;

use App\Filament\Resources\Clubs\Pages\CreateClub;
use App\Filament\Resources\Clubs\Pages\EditClub;
use App\Filament\Resources\Clubs\Pages\ListClubs;
use App\Filament\Resources\Clubs\Pages\ViewClub;
use App\Filament\Resources\Clubs\Schemas\ClubForm;
use App\Filament\Resources\Clubs\Schemas\ClubInfolist;
use App\Filament\Resources\Clubs\Tables\ClubsTable;
use App\Models\Club;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClubResource extends Resource
{
    protected static ?string $model = Club::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    public static function getNavigationGroup(): ?string
    {
        return 'Club Management';
    }

    public static function getNavigationLabel(): string
    {
        return 'Clubs';
    }

    public static function form(Schema $schema): Schema
    {
        return ClubForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ClubInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClubsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClubs::route('/'),
            'create' => CreateClub::route('/create'),
            'view' => ViewClub::route('/{record}'),
            'edit' => EditClub::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isSuperAdmin()) {
            return $query;
        }

        $clubIds = $user->accessibleClubIds();

        return empty($clubIds)
            ? $query->whereRaw('1 = 0')
            : $query->whereIn('clubs.id', $clubIds);
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return static::getEloquentQuery();
    }
}
