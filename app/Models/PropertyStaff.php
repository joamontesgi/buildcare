<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyStaff extends Model
{
    protected $table = 'property_staff';

    public $timestamps = false;

    protected $fillable = [
        'property_id',
        'employee_id',
        'role_id',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function staffRole(): BelongsTo
    {
        return $this->belongsTo(StaffRole::class, 'role_id');
    }
}
