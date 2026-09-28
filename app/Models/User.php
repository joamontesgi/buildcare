<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public const ROLE_ADMIN = 'admin';

    public const ROLE_CLERK = 'billing_clerk';

    public const ROLE_SUPERVISOR = 'supervisor';

    public const ROLE_CONTROLLER = 'scheduler_coordinator';

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function roleModel(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function roleSlug(): ?string
    {
        return $this->roleModel?->slug ?? $this->role;
    }

    public function isAdmin(): bool
    {
        if ($this->roleModel && $this->roleModel->is_admin) {
            return true;
        }

        return $this->role === self::ROLE_ADMIN;
    }

    public function canManageSchedule(): bool
    {
        if ($this->roleModel) {
            return $this->roleModel->is_admin || $this->roleModel->can_manage_schedule;
        }

        return in_array($this->role, [self::ROLE_ADMIN, Role::SLUG_SCHEDULER_COORDINATOR], true);
    }

    /**
     * @return array<string, string>
     */
    public static function roles(): array
    {
        $rows = Role::query()->orderBy('id')->get(['slug', 'name']);

        if ($rows->isEmpty()) {
            return collect(Role::catalog())->pluck('name', 'slug')->all();
        }

        return $rows->pluck('name', 'slug')->all();
    }
}
