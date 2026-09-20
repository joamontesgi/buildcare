<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zone extends Model
{
    protected $table = 'zones';

    protected $primaryKey = 'zone_id';

    protected $fillable = [
        'zone_name',
        'state',
    ];

    public function buildings(): HasMany
    {
        return $this->hasMany(Building::class, 'zone_id', 'zone_id');
    }
}
