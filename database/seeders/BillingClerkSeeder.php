<?php

namespace Database\Seeders;

use App\Models\BillingClerk;
use Illuminate\Database\Seeder;

class BillingClerkSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['code' => 'A-V', 'name' => 'A-V Team', 'email' => 'billing-av@buildcare.local', 'phone' => null],
            ['code' => 'N-O', 'name' => 'N-O Team', 'email' => 'billing-no@buildcare.local', 'phone' => null],
        ] as $row) {
            BillingClerk::query()->updateOrCreate(['code' => $row['code']], $row);
        }
    }
}
