<?php

namespace App\Filament\Resources\CourtSlots\Pages;

use App\Filament\Resources\CourtSlots\CourtSlotResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCourtSlot extends EditRecord
{
    protected static string $resource = CourtSlotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
