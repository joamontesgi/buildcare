<?php

namespace App\Support;

final class MatriculadoGrades
{
    public const MIN = 3.0;

    public const MAX = 5.0;

    public static function clamp(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        $number = max(self::MIN, min(self::MAX, (float) $value));

        return number_format($number, 2, '.', '');
    }

    /**
     * @return array{0: ?string, 1: ?string} [papa, promedio_actual]
     */
    public static function reconcile(?string $papa, ?string $promedioActual): array
    {
        $papa = self::clamp($papa);
        $promedioActual = self::clamp($promedioActual);

        if ($papa !== null && $promedioActual !== null && (float) $papa > (float) $promedioActual) {
            $papa = $promedioActual;
        }

        return [$papa, $promedioActual];
    }
}
