<?php

namespace App\Filament\Resources\Courts;

use App\Filament\Resources\Courts\Pages\CreateCourt;
use App\Filament\Resources\Courts\Pages\EditCourt;
use App\Filament\Resources\Courts\Pages\ListCourts;
use App\Filament\Resources\Courts\Pages\ViewCourt;
use App\Filament\Resources\Courts\Schemas\CourtForm;
use App\Filament\Resources\Courts\Schemas\CourtInfolist;
use App\Filament\Resources\Courts\Tables\CourtsTable;
use App\Models\Court;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CourtResource extends Resource
{
    protected static ?string $model = Court::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    public static function getNavigationGroup(): ?string
    {
        return 'Club Management';
    }

    public static function getNavigationLabel(): string
    {
        return 'Courts';
    }

    public static function form(Schema $schema): Schema
    {
        return CourtForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CourtInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CourtsTable::configure($table);
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
            'index' => ListCourts::route('/'),
            'create' => CreateCourt::route('/create'),
            'view' => ViewCourt::route('/{record}'),
            'edit' => EditCourt::route('/{record}/edit'),
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
            : $query->whereIn('courts.club_id', $clubIds);
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return static::getEloquentQuery();
    }
}
