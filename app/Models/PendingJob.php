<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendingJob extends Model
{
    protected $table = 'pending_jobs';

    protected $primaryKey = 'pending_id';

    protected $fillable = [
        'job_id',
        'reason_pending',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'job_id', 'job_id');
    }
}
