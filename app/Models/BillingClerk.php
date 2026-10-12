<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillingClerk extends Model
{
    use SoftDeletes;

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
