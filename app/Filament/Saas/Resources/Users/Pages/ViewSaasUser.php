<?php

namespace App\Filament\Saas\Resources\Users\Pages;

use App\Filament\Saas\Resources\Users\SaasUserResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSaasUser extends ViewRecord
{
    protected static string $resource = SaasUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
