<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingClerk extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'code',
        'name',
        'email',
        'phone',
    ];

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'billing_clerk_id');
    }
}
