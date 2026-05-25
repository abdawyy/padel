<?php

namespace App\Filament\Saas\Resources\AuditLogs\Pages;

use App\Filament\Saas\Resources\AuditLogs\AuditLogResource;
use Filament\Resources\Pages\ListRecords;

class ListAuditLogs extends ListRecords
{
    protected static string $resource = AuditLogResource::class;
}
