<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'email',
        'phone',
    ];

    public function workOrders(): BelongsToMany
    {
        return $this->belongsToMany(WorkOrder::class, 'work_order_vendors', 'vendor_id', 'work_order_id');
    }

    public function workOrderVendors(): HasMany
    {
        return $this->hasMany(WorkOrderVendor::class, 'vendor_id');
    }

    public function dailyStatusReports(): HasMany
    {
        return $this->hasMany(VendorDailyStatusReport::class, 'vendor_id');
    }
}
