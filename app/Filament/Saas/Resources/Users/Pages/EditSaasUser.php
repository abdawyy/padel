<?php

namespace App\Filament\Saas\Resources\Users\Pages;

use App\Filament\Saas\Resources\Users\SaasUserResource;
use Filament\Resources\Pages\EditRecord;

class EditSaasUser extends EditRecord
{
    protected static string $resource = SaasUserResource::class;
}
