<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'building_name',
        'address',
        'state_id',
        'management_id',
        'billing_clerk_id',
    ];

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function managementCompany(): BelongsTo
    {
        return $this->belongsTo(ManagementCompany::class, 'management_id');
    }

    public function billingClerk(): BelongsTo
    {
        return $this->belongsTo(BillingClerk::class, 'billing_clerk_id');
    }

    public function propertyStaff(): HasMany
    {
        return $this->hasMany(PropertyStaff::class, 'property_id');
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'property_id');
    }
}
