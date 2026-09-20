<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScheduleDay extends Model
{
    protected $table = 'schedule_days';

    protected $primaryKey = 'day_id';

    /** La PK es una fecha, no autoincrement. */
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'day_id',
        'day_of_week',
        'supervisor_on_call_id',
        'default_crews_note',
        'crews_confirmed_note',
        'bc_off_note',
        'crew_off_note',
    ];

    protected $casts = [
        'day_id' => 'date:Y-m-d',
    ];

    public function supervisorOnCall(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'supervisor_on_call_id', 'staff_id');
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'day_id', 'day_id');
    }
}
