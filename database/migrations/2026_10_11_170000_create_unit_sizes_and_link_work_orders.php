<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('unit_sizes')) {
            Schema::create('unit_sizes', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 100)->unique();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasColumn('work_orders', 'unit_size_id')) {
            Schema::table('work_orders', function (Blueprint $table): void {
                $table->foreignId('unit_size_id')->nullable()->after('unit_area')->constrained('unit_sizes');
            });
        }

        if (Schema::hasColumn('work_orders', 'size')) {
            $labels = DB::table('work_orders')
                ->whereNotNull('size')
                ->where('size', '!=', '')
                ->distinct()
                ->pluck('size');

            foreach ($labels as $label) {
                $id = DB::table('unit_sizes')->insertGetId([
                    'name' => $label,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('work_orders')->where('size', $label)->update(['unit_size_id' => $id]);
            }

            Schema::table('work_orders', function (Blueprint $table): void {
                $table->dropColumn('size');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('work_orders', 'size')) {
            Schema::table('work_orders', function (Blueprint $table): void {
                $table->string('size', 100)->nullable()->after('unit_area');
            });
        }

        if (Schema::hasColumn('work_orders', 'unit_size_id')) {
            $rows = DB::table('work_orders as wo')
                ->leftJoin('unit_sizes as us', 'wo.unit_size_id', '=', 'us.id')
                ->whereNotNull('wo.unit_size_id')
                ->get(['wo.id', 'us.name']);

            foreach ($rows as $row) {
                if ($row->name !== null) {
                    DB::table('work_orders')->where('id', $row->id)->update(['size' => $row->name]);
                }
            }

            Schema::table('work_orders', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('unit_size_id');
            });
        }

        Schema::dropIfExists('unit_sizes');
    }
};
