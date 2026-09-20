<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ManagementCompany */
class ManagementCompanyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'management_id' => $this->management_id,
            'name' => $this->name,
            'buildings_count' => $this->whenCounted('buildings'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
