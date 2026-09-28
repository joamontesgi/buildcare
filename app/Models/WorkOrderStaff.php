<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderStaff extends Model
{
    protected $table = 'work_order_staff';

    public $timestamps = false;

    protected $fillable = [
        'work_order_id',
        'employee_id',
        'role_id',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'work_order_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function staffRole(): BelongsTo
    {
        return $this->belongsTo(StaffRole::class, 'role_id');
    }
}
