<?php

namespace Database\Seeders;

use App\Models\RequestSource;
use Illuminate\Database\Seeder;

class RequestSourceSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'Request / PO / WTN / WO',
            'Phone request',
            'Email request',
            'Walk-in',
        ] as $name) {
            RequestSource::query()->firstOrCreate(['name' => $name]);
        }
    }
}
