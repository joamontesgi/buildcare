<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class State extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'code',
        'name',
    ];

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'state_id');
    }
}
