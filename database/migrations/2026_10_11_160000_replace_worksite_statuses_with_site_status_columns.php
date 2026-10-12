<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('properties', 'initial_site_status')) {
            Schema::table('properties', function (Blueprint $table): void {
                $table->string('initial_site_status', 20)->nullable()->after('billing_clerk_id');
            });
        }

        if (Schema::hasColumn('properties', 'initial_worksite_status_id') && Schema::hasTable('worksite_statuses')) {
            $rows = DB::table('properties')
                ->whereNotNull('initial_worksite_status_id')
                ->get(['id', 'initial_worksite_status_id']);

            foreach ($rows as $row) {
                $name = DB::table('worksite_statuses')
                    ->where('id', $row->initial_worksite_status_id)
                    ->value('name');

                if ($name !== null) {
                    DB::table('properties')->where('id', $row->id)->update(['initial_site_status' => $name]);
                }
            }

            Schema::table('properties', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('initial_worksite_status_id');
            });
        }

        if (! Schema::hasColumn('work_orders', 'site_status')) {
            Schema::table('work_orders', function (Blueprint $table): void {
                if (Schema::hasColumn('work_orders', 'unit_size_id')) {
                    $table->string('site_status', 20)->nullable()->after('unit_size_id');
                } elseif (Schema::hasColumn('work_orders', 'size')) {
                    $table->string('site_status', 20)->nullable()->after('size');
                } else {
                    $table->string('site_status', 20)->nullable()->after('unit_area');
                }
            });
        }

        if (Schema::hasColumn('work_orders', 'worksite_status_id') && Schema::hasTable('worksite_statuses')) {
            $rows = DB::table('work_orders')
                ->whereNotNull('worksite_status_id')
                ->get(['id', 'worksite_status_id']);

            foreach ($rows as $row) {
                $name = DB::table('worksite_statuses')
                    ->where('id', $row->worksite_status_id)
                    ->value('name');

                if ($name !== null && in_array($name, ['Occupied', 'Vacant'], true)) {
                    DB::table('work_orders')->where('id', $row->id)->update(['site_status' => $name]);
                }
            }

            Schema::table('work_orders', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('worksite_status_id');
            });
        }

        Schema::dropIfExists('worksite_statuses');
    }

    public function down(): void
    {
        if (! Schema::hasTable('worksite_statuses')) {
            Schema::create('worksite_statuses', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 100)->unique();
                $table->timestamps();
                $table->softDeletes();
            });

            foreach (['Occupied', 'Vacant'] as $name) {
                DB::table('worksite_statuses')->insert([
                    'name' => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if (! Schema::hasColumn('work_orders', 'worksite_status_id')) {
            Schema::table('work_orders', function (Blueprint $table): void {
                $table->foreignId('worksite_status_id')->nullable()->constrained('worksite_statuses');
            });
        }

        if (! Schema::hasColumn('properties', 'initial_worksite_status_id')) {
            Schema::table('properties', function (Blueprint $table): void {
                $table->foreignId('initial_worksite_status_id')->nullable()->constrained('worksite_statuses');
            });
        }

        if (Schema::hasColumn('work_orders', 'site_status')) {
            Schema::table('work_orders', function (Blueprint $table): void {
                $table->dropColumn('site_status');
            });
        }

        if (Schema::hasColumn('properties', 'initial_site_status')) {
            Schema::table('properties', function (Blueprint $table): void {
                $table->dropColumn('initial_site_status');
            });
        }
    }
};
