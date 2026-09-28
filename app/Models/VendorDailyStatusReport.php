<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorDailyStatusReport extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'vendor_daily_status_reports';

    protected $fillable = [
        'work_order_id',
        'vendor_id',
        'report_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'report_date' => 'date:Y-m-d',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'work_order_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }
}
