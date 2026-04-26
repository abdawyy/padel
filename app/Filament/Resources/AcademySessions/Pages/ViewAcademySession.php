<?php

namespace App\Filament\Resources\AcademySessions\Pages;

use App\Filament\Resources\AcademySessions\AcademySessionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAcademySession extends ViewRecord
{
    protected static string $resource = AcademySessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
