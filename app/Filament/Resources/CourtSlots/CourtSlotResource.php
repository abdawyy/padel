<?php

namespace App\Filament\Resources\CourtSlots;

use App\Filament\Resources\CourtSlots\Pages\CreateCourtSlot;
use App\Filament\Resources\CourtSlots\Pages\EditCourtSlot;
use App\Filament\Resources\CourtSlots\Pages\ListCourtSlots;
use App\Filament\Resources\CourtSlots\Pages\ViewCourtSlot;
use App\Filament\Resources\CourtSlots\Schemas\CourtSlotForm;
use App\Filament\Resources\CourtSlots\Schemas\CourtSlotInfolist;
use App\Filament\Resources\CourtSlots\Tables\CourtSlotsTable;
use App\Models\CourtSlot;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CourtSlotResource extends Resource
{
    protected static ?string $model = CourtSlot::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    public static function getNavigationGroup(): ?string
    {
        return 'Academy & Scheduling';
    }

    public static function getNavigationLabel(): string
    {
        return 'Court Slots';
    }

    public static function form(Schema $schema): Schema
    {
        return CourtSlotForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CourtSlotInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CourtSlotsTable::configure($table);
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
            'index' => ListCourtSlots::route('/'),
            'create' => CreateCourtSlot::route('/create'),
            'view' => ViewCourtSlot::route('/{record}'),
            'edit' => EditCourtSlot::route('/{record}/edit'),
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
            : $query->whereHas('court', fn (Builder $courtQuery) => $courtQuery->whereIn('club_id', $clubIds));
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return static::getEloquentQuery();
    }
}
