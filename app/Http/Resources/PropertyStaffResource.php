<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\PropertyStaff */
class PropertyStaffResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'property_id' => $this->property_id,
            'employee_id' => $this->employee_id,
            'role_id' => $this->role_id,
            'employee' => $this->whenLoaded('employee', fn () => new EmployeeResource($this->employee)),
            'staff_role' => $this->whenLoaded('staffRole', fn () => new StaffRoleResource($this->staffRole)),
        ];
    }
}
