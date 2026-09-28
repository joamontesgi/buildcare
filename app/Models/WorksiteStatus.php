<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorksiteStatus extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'worksite_status_id');
    }
}
