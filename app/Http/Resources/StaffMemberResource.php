<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffMemberResource extends JsonResource
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
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'club_role' => $this->whenPivotLoaded('club_users', fn () => $this->pivot?->role),
            'is_active' => (bool) $this->is_active,
            'can_access_admin' => $this->hasAdminAccess(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
