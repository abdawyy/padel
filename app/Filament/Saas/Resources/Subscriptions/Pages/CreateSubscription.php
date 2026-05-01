<?php

namespace App\Filament\Saas\Resources\Subscriptions\Pages;

use App\Filament\Saas\Resources\Subscriptions\SubscriptionResource;
use App\Models\ClubSaasSubscription;
use Filament\Resources\Pages\CreateRecord;

class CreateSubscription extends CreateRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function afterCreate(): void
    {
        /** @var ClubSaasSubscription $record */
        $record = $this->record;
        $record->syncClubStatus();
    }
}
