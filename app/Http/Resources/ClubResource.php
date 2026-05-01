<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClubResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sport_type' => $this->sport_type,
            'address' => $this->address,
            'subscription_status' => $this->subscription_status,
            'registration_status' => $this->registration_status,
            'settings' => $this->settings,
            'courts_count' => (int) ($this->courts_count ?? $this->courts()->count()),
            'saas_subscription' => $this->whenLoaded('activeSaasSubscription', function () {
                $sub = $this->activeSaasSubscription;
                if (! $sub) {
                    return null;
                }
                return [
                    'id'             => $sub->id,
                    'plan'           => $sub->relationLoaded('plan') && $sub->plan ? [
                        'id'   => $sub->plan->id,
                        'name' => $sub->plan->name,
                        'slug' => $sub->plan->slug,
                    ] : null,
                    'billing_cycle'  => $sub->billing_cycle,
                    'starts_at'      => $sub->starts_at?->toDateString(),
                    'ends_at'        => $sub->ends_at?->toDateString(),
                    'status'         => $sub->status,
                    'days_remaining' => $sub->daysRemaining(),
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
