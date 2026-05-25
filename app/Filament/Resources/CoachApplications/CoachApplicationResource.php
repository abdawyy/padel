<?php

namespace App\Filament\Resources\CoachApplications;

use App\Filament\Resources\CoachApplications\Pages\ListCoachApplications;
use App\Filament\Resources\CoachApplications\Pages\ViewCoachApplication;
use App\Filament\Resources\CoachApplications\Tables\CoachApplicationsTable;
use App\Models\CoachApplication;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CoachApplicationResource extends Resource
{
    protected static ?string $model = CoachApplication::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $navigationLabel = 'Coach Applications';

    public static function getNavigationGroup(): ?string
    {
        return 'Academy & Scheduling';
    }

    public static function table(Table $table): Table
    {
        return CoachApplicationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCoachApplications::route('/'),
            'view' => ViewCoachApplication::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['coach', 'session.club', 'session.court']);

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
            : $query->whereHas('session', fn (Builder $q) => $q->whereIn('club_id', $clubIds));
    }
}
