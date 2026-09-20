<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Staff extends Model
{
    protected $table = 'staff';

    protected $primaryKey = 'staff_id';

    protected $fillable = [
        'name',
        'role',
        'phone',
        'email',
    ];

    public const ROLE_SUPERVISOR = 'supervisor';

    public const ROLE_RUNNER = 'runner';

    public const ROLE_MANAGER = 'manager';

    public const ROLE_CLERK = 'clerk';

    /**
     * @return array<string, string>
     */
    public static function roles(): array
    {
        return [
            self::ROLE_SUPERVISOR => 'Supervisor',
            self::ROLE_RUNNER => 'Runner',
            self::ROLE_MANAGER => 'Manager',
            self::ROLE_CLERK => 'Clerk',
        ];
    }

    public function supervisedBuildings(): HasMany
    {
        return $this->hasMany(Building::class, 'bc_supervisor_id', 'staff_id');
    }

    public function onCallDays(): HasMany
    {
        return $this->hasMany(ScheduleDay::class, 'supervisor_on_call_id', 'staff_id');
    }
}
