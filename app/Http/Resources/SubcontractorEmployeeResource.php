<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\SubcontractorEmployee */
class SubcontractorEmployeeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'employee_id' => $this->employee_id,
            'subcontractor_id' => $this->subcontractor_id,
            'name' => $this->name,
            'phone' => $this->phone,
            'role' => $this->role,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
