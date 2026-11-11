<?php

namespace Tests\Unit;

use App\Support\MatriculadoGrades;
use PHPUnit\Framework\TestCase;

class MatriculadoGradesTest extends TestCase
{
    public function test_clamps_minimum_to_three(): void
    {
        $this->assertSame('3.00', MatriculadoGrades::clamp('2.10'));
    }

    public function test_reconcile_caps_papa_to_promedio(): void
    {
        [$papa, $promedio] = MatriculadoGrades::reconcile('4.20', '3.80');

        $this->assertSame('3.80', $papa);
        $this->assertSame('3.80', $promedio);
    }
}
