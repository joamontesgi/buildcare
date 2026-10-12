<?php

namespace App\Models;

use App\Support\WorkOrderJobStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrder extends Model
{
    use SoftDeletes;

    public const FIRST_ORDER_CODE = 2015;

    protected static function booted(): void
    {
        static::creating(function (WorkOrder $order): void {
            if ($order->order_code === null) {
                $order->order_code = static::nextOrderCode();
            }

            if ($order->job_status_id === null) {
                $order->job_status_id = WorkOrderJobStatus::scheduledId();
            }
        });
    }

    protected $casts = [
        'bc_estimate' => 'integer',
        'execution_date' => 'date:Y-m-d',
    ];

    protected $fillable = [
        'order_code',
        'execution_date',
        'property_id',
        'unit_area',
        'unit_size_id',
        'site_status',
        'job_status_id',
        'job_description',
        'bc_estimate',
        'extras',
        'special_notes_sequence',
        'source',
        'request_source_id',
    ];

    public static function nextOrderCode(): int
    {
        $last = static::withTrashed()
            ->lockForUpdate()
            ->orderByDesc('order_code')
            ->value('order_code');

        $next = ($last ?? (self::FIRST_ORDER_CODE - 1)) + 1;

        return max($next, self::FIRST_ORDER_CODE);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     * @return \Illuminate\Database\Eloquent\Builder<self>
     */
    public function scopeOrderedForDisplay($query)
    {
        return $query
            ->orderBy('execution_date')
            ->orderBy('order_code')
            ->orderBy('id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function unitSize(): BelongsTo
    {
        return $this->belongsTo(UnitSize::class, 'unit_size_id')->withTrashed();
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
