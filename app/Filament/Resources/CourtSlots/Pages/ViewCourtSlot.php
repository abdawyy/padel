<?php

namespace App\Filament\Resources\CourtSlots\Pages;

use App\Filament\Resources\CourtSlots\CourtSlotResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCourtSlot extends ViewRecord
{
    protected static string $resource = CourtSlotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
