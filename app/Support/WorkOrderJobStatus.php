<?php

namespace App\Support;

use App\Models\JobStatus;
use Illuminate\Validation\Rule;

/** Job status workflow for work orders (English labels). */
final class WorkOrderJobStatus
{
    public const SCHEDULED = 'Scheduled';

    public const CANCELLED = 'Cancelled';

    public const RESCHEDULED = 'Rescheduled';

    public const CONTINUES = 'Continues';

    public const DONE = 'Done';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::SCHEDULED,
            self::CANCELLED,
            self::RESCHEDULED,
            self::CONTINUES,
            self::DONE,
        ];
    }

    /**
     * Statuses the user may set on an existing work order (any time, any number of times).
     *
     * @return list<string>
     */
    public static function transitionNames(): array
    {
        return self::all();
    }

    public static function idFor(string $name): int
    {
        return (int) JobStatus::query()->where('name', $name)->value('id');
    }

    public static function scheduledId(): int
    {
        return self::idFor(self::SCHEDULED);
    }

    /**
     * @return array<string, string>
     */
    public static function transitionOptions(): array
    {
        $options = [];
        foreach (self::transitionNames() as $name) {
            $options[$name] = $name;
        }

        return $options;
    }

    public static function transitionRule(): \Illuminate\Validation\Rules\In
    {
        return Rule::in(self::transitionNames());
    }

}
