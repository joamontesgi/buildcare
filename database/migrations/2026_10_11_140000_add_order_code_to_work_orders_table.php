<?php

use App\Models\WorkOrder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('work_orders', 'order_code')) {
            return;
        }

        Schema::table('work_orders', function (Blueprint $table): void {
            $table->unsignedInteger('order_code')->nullable()->unique()->after('id');
        });

        $code = 2014;
        WorkOrder::withTrashed()->orderBy('id')->each(function (WorkOrder $order) use (&$code): void {
            $code++;
            $order->forceFill(['order_code' => $code])->saveQuietly();
        });

        Schema::table('work_orders', function (Blueprint $table): void {
            $table->unsignedInteger('order_code')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('work_orders', 'order_code')) {
            return;
        }

        Schema::table('work_orders', function (Blueprint $table): void {
            $table->dropUnique(['order_code']);
            $table->dropColumn('order_code');
        });
    }
};
