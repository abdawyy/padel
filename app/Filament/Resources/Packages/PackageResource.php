<?php

namespace App\Filament\Resources\Packages;

use App\Filament\Resources\Packages\Pages\CreatePackage;
use App\Filament\Resources\Packages\Pages\EditPackage;
use App\Filament\Resources\Packages\Pages\ListPackages;
use App\Filament\Resources\Packages\Pages\ViewPackage;
use App\Filament\Resources\Packages\RelationManagers\SubscribersRelationManager;
use App\Filament\Resources\Packages\Schemas\PackageForm;
use App\Filament\Resources\Packages\Schemas\PackageInfolist;
use App\Filament\Resources\Packages\Tables\PackagesTable;
use App\Models\Package;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PackageResource extends Resource
{
    protected static ?string $model = Package::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    public static function getNavigationGroup(): ?string
    {
        return 'Academy & Scheduling';
    }

    public static function getNavigationLabel(): string
    {
        return 'Packages';
    }

    public static function form(Schema $schema): Schema
    {
        return PackageForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PackageInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PackagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            SubscribersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPackages::route('/'),
            'create' => CreatePackage::route('/create'),
            'view'   => ViewPackage::route('/{record}'),
            'edit'   => EditPackage::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);

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
            : $query->whereIn('club_id', $clubIds);
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return static::getEloquentQuery();
    }
}
