<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
    ];

    public function propertyStaff(): HasMany
    {
        return $this->hasMany(PropertyStaff::class, 'employee_id');
    }

    public function workOrderStaff(): HasMany
    {
        return $this->hasMany(WorkOrderStaff::class, 'employee_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.($this->last_name ?? ''));
    }
}
