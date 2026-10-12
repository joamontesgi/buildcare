<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrderStaff extends Model
{
    use SoftDeletes;

    protected $table = 'work_order_staff';

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
