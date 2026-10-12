<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManagementCompany extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
    ];

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'management_id');
    }
}
