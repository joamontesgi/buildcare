<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subcontractor extends Model
{
    protected $table = 'subcontractors';

    protected $primaryKey = 'subcontractor_id';

    protected $fillable = [
        'company_name',
        'contact_name',
        'phone',
        'capabilities',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(SubcontractorEmployee::class, 'subcontractor_id', 'subcontractor_id');
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'subcontractor_id', 'subcontractor_id');
    }
}
