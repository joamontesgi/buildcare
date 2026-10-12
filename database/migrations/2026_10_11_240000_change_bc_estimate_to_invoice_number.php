<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('work_orders', 'bc_estimate')) {
            return;
        }

        $rows = DB::table('work_orders')->whereNotNull('bc_estimate')->get(['id', 'bc_estimate']);

        foreach ($rows as $row) {
            $raw = trim((string) $row->bc_estimate);
            if ($raw === '' || ! is_numeric($raw)) {
                DB::table('work_orders')->where('id', $row->id)->update(['bc_estimate' => null]);

                continue;
            }

            DB::table('work_orders')->where('id', $row->id)->update([
                'bc_estimate' => (int) round((float) $raw),
            ]);
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE work_orders MODIFY bc_estimate BIGINT UNSIGNED NULL');

            return;
        }

        Schema::table('work_orders', function (Blueprint $table): void {
            $table->unsignedBigInteger('bc_estimate')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('work_orders', 'bc_estimate')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE work_orders MODIFY bc_estimate DECIMAL(12, 2) NULL');

            return;
        }

        Schema::table('work_orders', function (Blueprint $table): void {
            $table->decimal('bc_estimate', 12, 2)->nullable()->change();
        });
    }
};
