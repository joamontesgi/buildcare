<?php

namespace Database\Seeders;

use App\Models\Matriculado;
use Illuminate\Database\Seeder;

class MatriculadoSeeder extends Seeder
{
    public const COUNT = 2000;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Matriculado::query()->delete();

        Matriculado::factory()
            ->count(self::COUNT)
            ->create();
    }
}
