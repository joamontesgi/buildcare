<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\VendorDailyStatusReport */
class VendorDailyStatusReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'work_order_id' => $this->work_order_id,
            'vendor_id' => $this->vendor_id,
            'report_date' => optional($this->report_date)->format('Y-m-d'),
            'status' => $this->status,
            'notes' => $this->notes,
            'vendor' => $this->whenLoaded('vendor', fn () => $this->vendor ? new VendorResource($this->vendor) : null),
            'created_at' => $this->created_at,
        ];
    }
}
