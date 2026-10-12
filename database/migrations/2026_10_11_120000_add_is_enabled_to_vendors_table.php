<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table): void {
            if (! Schema::hasColumn('vendors', 'is_enabled')) {
                $table->boolean('is_enabled')->default(true)->after('phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table): void {
            if (Schema::hasColumn('vendors', 'is_enabled')) {
                $table->dropColumn('is_enabled');
            }
        });
    }
};
