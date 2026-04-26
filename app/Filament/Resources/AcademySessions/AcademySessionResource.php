<?php

namespace App\Filament\Resources\AcademySessions;

use App\Filament\Resources\AcademySessions\Pages\CreateAcademySession;
use App\Filament\Resources\AcademySessions\Pages\EditAcademySession;
use App\Filament\Resources\AcademySessions\Pages\ListAcademySessions;
use App\Filament\Resources\AcademySessions\Pages\ViewAcademySession;
use App\Filament\Resources\AcademySessions\Schemas\AcademySessionForm;
use App\Filament\Resources\AcademySessions\Schemas\AcademySessionInfolist;
use App\Filament\Resources\AcademySessions\Tables\AcademySessionsTable;
use App\Models\AcademySession;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AcademySessionResource extends Resource
{
    protected static ?string $model = AcademySession::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    public static function getNavigationGroup(): ?string
    {
        return 'Academy & Scheduling';
    }

    public static function getNavigationLabel(): string
    {
        return 'Academy Sessions';
    }

    public static function form(Schema $schema): Schema
    {
        return AcademySessionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AcademySessionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AcademySessionsTable::configure($table);
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
            'index' => ListAcademySessions::route('/'),
            'create' => CreateAcademySession::route('/create'),
            'view' => ViewAcademySession::route('/{record}'),
            'edit' => EditAcademySession::route('/{record}/edit'),
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
            : $query->whereIn('academy_sessions.club_id', $clubIds);
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return static::getEloquentQuery();
    }
}
