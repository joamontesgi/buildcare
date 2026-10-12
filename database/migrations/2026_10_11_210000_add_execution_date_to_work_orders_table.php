<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('work_orders', 'execution_date')) {
            return;
        }

        Schema::table('work_orders', function (Blueprint $table): void {
            $table->date('execution_date')->nullable()->after('order_code');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('work_orders', 'execution_date')) {
            return;
        }

        Schema::table('work_orders', function (Blueprint $table): void {
            $table->dropColumn('execution_date');
        });
    }
};
