<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Property */
class PropertyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'building_name' => $this->building_name,
            'address' => $this->address,
            'state_id' => $this->state_id,
            'management_id' => $this->management_id,
            'billing_clerk_id' => $this->billing_clerk_id,
            'state' => $this->whenLoaded('state', fn () => new StateResource($this->state)),
            'management_company' => $this->whenLoaded('managementCompany', fn () => new ManagementCompanyResource($this->managementCompany)),
            'billing_clerk' => $this->whenLoaded('billingClerk', fn () => new BillingClerkResource($this->billingClerk)),
            'property_staff' => PropertyStaffResource::collection($this->whenLoaded('propertyStaff')),
        ];
    }
}
