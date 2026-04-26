<?php

namespace App\Filament\Resources\AcademySessions\Pages;

use App\Filament\Resources\AcademySessions\AcademySessionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAcademySession extends EditRecord
{
    protected static string $resource = AcademySessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
