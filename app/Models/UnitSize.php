<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnitSize extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
    ];

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'unit_size_id');
    }
}
