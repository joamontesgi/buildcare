<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('work_orders', 'bc_work_order')) {
            Schema::table('work_orders', function (Blueprint $table): void {
                $table->dropColumn('bc_work_order');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('work_orders', 'bc_work_order')) {
            Schema::table('work_orders', function (Blueprint $table): void {
                $table->string('bc_work_order', 100)->nullable()->after('job_description');
            });
        }
    }
};
