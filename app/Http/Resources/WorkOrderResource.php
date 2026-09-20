<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\WorkOrder */
class WorkOrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'job_id' => $this->job_id,
            'day_id' => optional($this->day_id)->format('Y-m-d'),
            'building_id' => $this->building_id,
            'subcontractor_id' => $this->subcontractor_id,
            'unit_area' => $this->unit_area,
            'size' => $this->size,
            'worksite_status' => $this->worksite_status,
            'job_description' => $this->job_description,
            'request_po_wtn_wo' => $this->request_po_wtn_wo,
            'bc_work_order' => $this->bc_work_order,
            'bc_estimate_ref' => $this->bc_estimate_ref,
            'extras' => $this->extras,
            'special_notes' => $this->special_notes,
            'vendor_status_report' => $this->vendor_status_report,
            'assigned_employee_ids' => $this->assigned_employee_ids ?? [],
            'building' => $this->whenLoaded('building', fn () => $this->building ? new BuildingResource($this->building) : null),
            'subcontractor' => $this->whenLoaded('subcontractor', fn () => $this->subcontractor ? new SubcontractorResource($this->subcontractor) : null),
            'schedule_day' => $this->whenLoaded('scheduleDay', fn () => $this->scheduleDay ? new ScheduleDayResource($this->scheduleDay) : null),
            'pending_job' => $this->whenLoaded('pendingJob', fn () => $this->pendingJob ? new PendingJobResource($this->pendingJob) : null),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
