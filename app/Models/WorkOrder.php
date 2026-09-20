<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WorkOrder extends Model
{
    protected $table = 'work_orders';

    protected $primaryKey = 'job_id';

    protected $fillable = [
        'day_id',
        'building_id',
        'subcontractor_id',
        'unit_area',
        'size',
        'worksite_status',
        'job_description',
        'request_po_wtn_wo',
        'bc_work_order',
        'bc_estimate_ref',
        'extras',
        'special_notes',
        'vendor_status_report',
        'assigned_employee_ids',
    ];

    protected $casts = [
        'day_id' => 'date:Y-m-d',
        'assigned_employee_ids' => 'array',
    ];

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_PENDING = 'pending';

    public const STATUS_CANCELLED = 'cancelled';

    /**
     * @return array<string, string>
     */
    public static function worksiteStatuses(): array
    {
        return [
            self::STATUS_SCHEDULED => 'Scheduled',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_PENDING => 'Pending',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    public function scheduleDay(): BelongsTo
    {
        return $this->belongsTo(ScheduleDay::class, 'day_id', 'day_id');
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class, 'building_id', 'building_id');
    }

    public function subcontractor(): BelongsTo
    {
        return $this->belongsTo(Subcontractor::class, 'subcontractor_id', 'subcontractor_id');
    }

    public function pendingJob(): HasOne
    {
        return $this->hasOne(PendingJob::class, 'job_id', 'job_id');
    }
}
