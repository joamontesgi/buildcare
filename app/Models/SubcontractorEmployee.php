<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubcontractorEmployee extends Model
{
    protected $table = 'subcontractor_employees';

    protected $primaryKey = 'employee_id';

    protected $fillable = [
        'subcontractor_id',
        'name',
        'phone',
        'role',
    ];

    public function subcontractor(): BelongsTo
    {
        return $this->belongsTo(Subcontractor::class, 'subcontractor_id', 'subcontractor_id');
    }
}
