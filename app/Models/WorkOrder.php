<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkOrder extends Model
{
    public const UPDATED_AT = 'updated_at';

    public const CREATED_AT = 'created_at';

    protected $fillable = [
        'property_id',
        'unit_area',
        'size',
        'worksite_status_id',
        'job_status_id',
        'job_description',
        'bc_work_order',
        'bc_estimate',
        'extras',
        'special_notes_sequence',
        'request_source_id',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function worksiteStatus(): BelongsTo
    {
        return $this->belongsTo(WorksiteStatus::class, 'worksite_status_id');
    }

    public function jobStatus(): BelongsTo
    {
        return $this->belongsTo(JobStatus::class, 'job_status_id');
    }

    public function requestSource(): BelongsTo
    {
        return $this->belongsTo(RequestSource::class, 'request_source_id');
    }

    public function references(): HasMany
    {
        return $this->hasMany(WorkOrderReference::class, 'work_order_id');
    }

    public function staffAssignments(): HasMany
    {
        return $this->hasMany(WorkOrderStaff::class, 'work_order_id');
    }

    public function workOrderVendors(): HasMany
    {
        return $this->hasMany(WorkOrderVendor::class, 'work_order_id');
    }

    public function vendorDailyStatusReports(): HasMany
    {
        return $this->hasMany(VendorDailyStatusReport::class, 'work_order_id');
    }
}
