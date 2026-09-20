<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Zone */
class ZoneResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'zone_id' => $this->zone_id,
            'zone_name' => $this->zone_name,
            'state' => $this->state,
            'buildings_count' => $this->whenCounted('buildings'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
