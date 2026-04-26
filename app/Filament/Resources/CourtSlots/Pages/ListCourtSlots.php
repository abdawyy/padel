<?php

namespace App\Filament\Resources\CourtSlots\Pages;

use App\Filament\Resources\CourtSlots\CourtSlotResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCourtSlots extends ListRecords
{
    protected static string $resource = CourtSlotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
