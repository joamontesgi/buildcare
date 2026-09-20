<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Building */
class BuildingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'building_id' => $this->building_id,
            'name' => $this->name,
            'address' => $this->address,
            'state' => $this->state,
            'management_id' => $this->management_id,
            'zone_id' => $this->zone_id,
            'bc_supervisor_id' => $this->bc_supervisor_id,
            'billing_clerk_code' => $this->billing_clerk_code,
            'travel_expense' => $this->travel_expense,
            'paint_specs' => $this->paint_specs,
            'notes' => $this->notes,
            'management_company' => $this->whenLoaded('managementCompany', fn () => $this->managementCompany ? new ManagementCompanyResource($this->managementCompany) : null),
            'zone' => $this->whenLoaded('zone', fn () => $this->zone ? new ZoneResource($this->zone) : null),
            'bc_supervisor' => $this->whenLoaded('bcSupervisor', fn () => $this->bcSupervisor ? new StaffResource($this->bcSupervisor) : null),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
