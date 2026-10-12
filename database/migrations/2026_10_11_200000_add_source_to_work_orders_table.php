<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('work_orders', 'source')) {
            return;
        }

        Schema::table('work_orders', function (Blueprint $table): void {
            $table->text('source')->nullable()->after('special_notes_sequence');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('work_orders', 'source')) {
            return;
        }

        Schema::table('work_orders', function (Blueprint $table): void {
            $table->dropColumn('source');
        });
    }
};
