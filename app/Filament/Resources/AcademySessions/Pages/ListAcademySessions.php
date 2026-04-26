<?php

namespace App\Filament\Resources\AcademySessions\Pages;

use App\Filament\Resources\AcademySessions\AcademySessionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAcademySessions extends ListRecords
{
    protected static string $resource = AcademySessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
