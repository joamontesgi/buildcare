<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ManagementCompany extends Model
{
    protected $table = 'management_companies';

    protected $primaryKey = 'management_id';

    protected $fillable = [
        'name',
    ];

    public function buildings(): HasMany
    {
        return $this->hasMany(Building::class, 'management_id', 'management_id');
    }
}
