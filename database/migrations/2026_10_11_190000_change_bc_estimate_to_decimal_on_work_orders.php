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

        $numericTypes = ['decimal', 'float', 'double', 'numeric'];
        if (in_array(Schema::getColumnType('work_orders', 'bc_estimate'), $numericTypes, true)) {
            return;
        }

        $rows = DB::table('work_orders')->whereNotNull('bc_estimate')->get(['id', 'bc_estimate']);

        foreach ($rows as $row) {
            $raw = trim((string) $row->bc_estimate);
            $value = is_numeric($raw) ? $raw : null;

            DB::table('work_orders')->where('id', $row->id)->update(['bc_estimate' => $value]);
        }

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE work_orders MODIFY bc_estimate DECIMAL(12, 2) NULL');

            return;
        }

        Schema::table('work_orders', function (Blueprint $table): void {
            $table->decimal('bc_estimate', 12, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('work_orders', 'bc_estimate')) {
            return;
        }

        if (Schema::getColumnType('work_orders', 'bc_estimate') === 'string') {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE work_orders MODIFY bc_estimate VARCHAR(100) NULL');

            return;
        }

        Schema::table('work_orders', function (Blueprint $table): void {
            $table->string('bc_estimate', 100)->nullable()->change();
        });
    }
};
