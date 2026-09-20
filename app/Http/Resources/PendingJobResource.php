<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\PendingJob */
class PendingJobResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'pending_id' => $this->pending_id,
            'job_id' => $this->job_id,
            'reason_pending' => $this->reason_pending,
            'work_order' => $this->whenLoaded('workOrder', fn () => $this->workOrder ? new WorkOrderResource($this->workOrder) : null),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
