<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Building extends Model
{
    protected $table = 'buildings';

    protected $primaryKey = 'building_id';

    protected $fillable = [
        'name',
        'address',
        'state',
        'management_id',
        'zone_id',
        'bc_supervisor_id',
        'billing_clerk_code',
        'travel_expense',
        'paint_specs',
        'notes',
    ];

    public function managementCompany(): BelongsTo
    {
        return $this->belongsTo(ManagementCompany::class, 'management_id', 'management_id');
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class, 'zone_id', 'zone_id');
    }

    public function bcSupervisor(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'bc_supervisor_id', 'staff_id');
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'building_id', 'building_id');
    }
}
