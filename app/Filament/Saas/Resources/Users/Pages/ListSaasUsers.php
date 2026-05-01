<?php

namespace App\Filament\Saas\Resources\Users\Pages;

use App\Filament\Saas\Resources\Users\SaasUserResource;
use Filament\Resources\Pages\ListRecords;

class ListSaasUsers extends ListRecords
{
    protected static string $resource = SaasUserResource::class;
}
