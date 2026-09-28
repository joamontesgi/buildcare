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
            'id' => $this->id,
            'property_id' => $this->property_id,
            'unit_area' => $this->unit_area,
            'size' => $this->size,
            'worksite_status_id' => $this->worksite_status_id,
            'job_status_id' => $this->job_status_id,
            'job_description' => $this->job_description,
            'bc_work_order' => $this->bc_work_order,
            'bc_estimate' => $this->bc_estimate,
            'extras' => $this->extras,
            'special_notes_sequence' => $this->special_notes_sequence,
            'request_source_id' => $this->request_source_id,
            'property' => $this->whenLoaded('property', fn () => new PropertyResource($this->property)),
            'worksite_status' => $this->whenLoaded('worksiteStatus', fn () => new WorksiteStatusResource($this->worksiteStatus)),
            'job_status' => $this->whenLoaded('jobStatus', fn () => $this->jobStatus ? new JobStatusResource($this->jobStatus) : null),
            'request_source' => $this->whenLoaded('requestSource', fn () => $this->requestSource ? new RequestSourceResource($this->requestSource) : null),
            'references' => WorkOrderReferenceResource::collection($this->whenLoaded('references')),
            'staff' => WorkOrderStaffResource::collection($this->whenLoaded('staffAssignments')),
            'vendors' => VendorResource::collection(
                $this->whenLoaded('workOrderVendors', fn () => $this->workOrderVendors->map->vendor)
            ),
            'vendor_daily_status_reports' => VendorDailyStatusReportResource::collection($this->whenLoaded('vendorDailyStatusReports')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
