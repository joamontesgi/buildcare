<?php

namespace Database\Seeders;

use App\Models\State;
use Illuminate\Database\Seeder;

class StateSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['code' => 'NJ', 'name' => 'New Jersey'],
            ['code' => 'NY', 'name' => 'New York'],
            ['code' => 'PA', 'name' => 'Pennsylvania'],
        ];

        foreach ($rows as $row) {
            State::query()->updateOrCreate(['code' => $row['code']], $row);
        }
    }
}
