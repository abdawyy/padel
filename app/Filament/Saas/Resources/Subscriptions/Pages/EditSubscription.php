<?php

namespace App\Filament\Saas\Resources\Subscriptions\Pages;

use App\Filament\Saas\Resources\Subscriptions\SubscriptionResource;
use App\Models\ClubSaasSubscription;
use Filament\Resources\Pages\EditRecord;

class EditSubscription extends EditRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function afterSave(): void
    {
        /** @var ClubSaasSubscription $record */
        $record = $this->record;
        $record->syncClubStatus();
    }
}
