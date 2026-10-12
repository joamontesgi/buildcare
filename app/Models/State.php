<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class State extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
    ];

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'state_id');
    }
}
