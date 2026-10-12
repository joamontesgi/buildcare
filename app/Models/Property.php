<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'building_name',
        'address',
        'city',
        'zip_code',
        'state_id',
        'management_id',
        'billing_clerk_id',
        'initial_site_status',
    ];

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state_id')->withTrashed();
    }

    public function managementCompany(): BelongsTo
    {
        return $this->belongsTo(ManagementCompany::class, 'management_id')->withTrashed();
    }

    public function billingClerk(): BelongsTo
    {
        return $this->belongsTo(BillingClerk::class, 'billing_clerk_id')->withTrashed();
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
