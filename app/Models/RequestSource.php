<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestSource extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
    ];

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'request_source_id');
    }
}
