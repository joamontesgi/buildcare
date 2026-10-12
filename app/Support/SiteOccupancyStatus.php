<?php

namespace App\Support;

use Illuminate\Validation\Rule;

/** Estado inicial del inmueble / site status en work orders (sin tabla catálogo). */
final class SiteOccupancyStatus
{
    public const OCCUPIED = 'Occupied';

    public const VACANT = 'Vacant';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [self::OCCUPIED, self::VACANT];
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::OCCUPIED => self::OCCUPIED,
            self::VACANT => self::VACANT,
        ];
    }

    public static function nullableRule(): \Illuminate\Validation\Rules\In
    {
        return Rule::in(self::all());
    }
}
