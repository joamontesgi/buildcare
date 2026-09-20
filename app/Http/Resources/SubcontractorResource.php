<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Subcontractor */
class SubcontractorResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'subcontractor_id' => $this->subcontractor_id,
            'company_name' => $this->company_name,
            'contact_name' => $this->contact_name,
            'phone' => $this->phone,
            'capabilities' => $this->capabilities,
            'employees_count' => $this->whenCounted('employees'),
            'employees' => SubcontractorEmployeeResource::collection($this->whenLoaded('employees')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
