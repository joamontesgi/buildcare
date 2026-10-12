<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Roles operativos en propiedades / órdenes (tabla roles en bc.sql). */
class StaffRole extends Model
{
    use SoftDeletes;

    protected $table = 'roles';

    protected $fillable = [
        'name',
    ];

    public function propertyStaff(): HasMany
    {
        return $this->hasMany(PropertyStaff::class, 'role_id');
    }

    public function workOrderStaff(): HasMany
    {
        return $this->hasMany(WorkOrderStaff::class, 'role_id');
    }
}
