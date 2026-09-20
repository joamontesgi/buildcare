<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\User */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->roleSlug(),
            'role_id' => $this->role_id,
            'role_model' => $this->whenLoaded('roleModel', fn () => $this->roleModel ? new RoleResource($this->roleModel) : null),
            'is_admin' => $this->isAdmin(),
            'can_manage_schedule' => $this->canManageSchedule(),
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
        ];
    }
}
