<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $table = 'roles';

    protected $primaryKey = 'role_id';

    protected $fillable = [
        'slug',
        'name',
        'description',
        'can_manage_schedule',
        'is_admin',
    ];

    protected $casts = [
        'can_manage_schedule' => 'boolean',
        'is_admin' => 'boolean',
    ];

    // Slugs canónicos del negocio (usados por seeders y middleware).
    public const SLUG_ADMIN = 'admin';

    public const SLUG_SCHEDULER_COORDINATOR = 'scheduler_coordinator';

    public const SLUG_BILLING_CLERK = 'billing_clerk';

    public const SLUG_PROJECT_REPORTER_COORDINATOR = 'project_reporter_coordinator';

    public const SLUG_PROPOSAL_COORDINATOR = 'proposal_coordinator';

    public const SLUG_WAREHOUSE_COORDINATOR = 'warehouse_coordinator';

    public const SLUG_SUPERVISOR = 'supervisor';

    /**
     * Los 7 roles del negocio, en el orden en que se muestran.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function catalog(): array
    {
        return [
            [
                'slug' => self::SLUG_ADMIN,
                'name' => 'Administrator',
                'description' => 'Full access to the system.',
                'is_admin' => true,
                'can_manage_schedule' => true,
            ],
            [
                'slug' => self::SLUG_SCHEDULER_COORDINATOR,
                'name' => 'Scheduler Coordinator',
                'description' => 'Builds and maintains the daily schedule.',
                'is_admin' => false,
                'can_manage_schedule' => true,
            ],
            [
                'slug' => self::SLUG_BILLING_CLERK,
                'name' => 'Billing Clerk',
                'description' => 'Billing and BC codes.',
                'is_admin' => false,
                'can_manage_schedule' => false,
            ],
            [
                'slug' => self::SLUG_PROJECT_REPORTER_COORDINATOR,
                'name' => 'Project Reporter Coordinator',
                'description' => 'Coordinates project reporting.',
                'is_admin' => false,
                'can_manage_schedule' => false,
            ],
            [
                'slug' => self::SLUG_PROPOSAL_COORDINATOR,
                'name' => 'Proposal Coordinator',
                'description' => 'Coordinates proposals.',
                'is_admin' => false,
                'can_manage_schedule' => false,
            ],
            [
                'slug' => self::SLUG_WAREHOUSE_COORDINATOR,
                'name' => 'Warehouse Coordinator',
                'description' => 'Coordinates warehouse operations.',
                'is_admin' => false,
                'can_manage_schedule' => false,
            ],
            [
                'slug' => self::SLUG_SUPERVISOR,
                'name' => 'Supervisor',
                'description' => 'Field supervisor.',
                'is_admin' => false,
                'can_manage_schedule' => false,
            ],
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id', 'role_id');
    }
}
