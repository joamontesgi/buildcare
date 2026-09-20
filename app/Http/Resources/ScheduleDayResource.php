<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ScheduleDay */
class ScheduleDayResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'day_id' => optional($this->day_id)->format('Y-m-d'),
            'day_of_week' => $this->day_of_week,
            'supervisor_on_call_id' => $this->supervisor_on_call_id,
            'default_crews_note' => $this->default_crews_note,
            'crews_confirmed_note' => $this->crews_confirmed_note,
            'bc_off_note' => $this->bc_off_note,
            'crew_off_note' => $this->crew_off_note,
            'supervisor_on_call' => $this->whenLoaded('supervisorOnCall', fn () => $this->supervisorOnCall ? new StaffResource($this->supervisorOnCall) : null),
            'work_orders_count' => $this->whenCounted('workOrders'),
            'work_orders' => WorkOrderResource::collection($this->whenLoaded('workOrders')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
