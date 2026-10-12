<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Esquema alineado con bc.sql (bc_management).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_clerks', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 150)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('employees', function (Blueprint $table): void {
            $table->id();
            $table->string('first_name', 100);
            $table->string('last_name', 100)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('job_statuses', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100)->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('management_companies', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 150)->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('states', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name', 100);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100)->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('properties', function (Blueprint $table): void {
            $table->id();
            $table->string('building_name', 200);
            $table->string('address', 300);
            $table->string('city', 100)->default('');
            $table->string('zip_code', 20)->default('');
            $table->foreignId('state_id')->constrained('states');
            $table->foreignId('management_id')->constrained('management_companies');
            $table->foreignId('billing_clerk_id')->nullable()->constrained('billing_clerks');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('property_staff', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->constrained('properties');
            $table->foreignId('employee_id')->constrained('employees');
            $table->foreignId('role_id')->constrained('roles');
            $table->unique(['property_id', 'employee_id', 'role_id'], 'uq_property_employee_role');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('vendors', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 200);
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('request_sources', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100)->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('unit_sizes', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100)->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('properties', function (Blueprint $table): void {
            $table->string('initial_site_status', 20)->nullable()->after('billing_clerk_id');
        });

        Schema::create('work_orders', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('order_code')->unique();
            $table->date('execution_date')->nullable();
            $table->foreignId('property_id')->constrained('properties');
            $table->string('unit_area', 100)->nullable();
            $table->foreignId('unit_size_id')->nullable()->constrained('unit_sizes');
            $table->string('site_status', 20)->nullable();
            $table->foreignId('job_status_id')->nullable()->constrained('job_statuses');
            $table->text('job_description')->nullable();
            $table->decimal('bc_estimate', 12, 2)->nullable();
            $table->text('extras')->nullable();
            $table->text('special_notes_sequence')->nullable();
            $table->text('source')->nullable();
            $table->foreignId('request_source_id')->nullable()->constrained('request_sources');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('work_order_references', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders');
            $table->string('reference_type', 30);
            $table->string('reference_number', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('work_order_staff', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders');
            $table->foreignId('employee_id')->constrained('employees');
            $table->foreignId('role_id')->constrained('roles');
            $table->unique(['work_order_id', 'employee_id', 'role_id'], 'uq_work_order_employee_role');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('work_order_vendors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders');
            $table->foreignId('vendor_id')->constrained('vendors');
            $table->unique(['work_order_id', 'vendor_id'], 'uq_work_order_vendor');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('vendor_daily_status_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders');
            $table->foreignId('vendor_id')->nullable()->constrained('vendors');
            $table->date('report_date');
            $table->string('status', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_daily_status_reports');
        Schema::dropIfExists('work_order_vendors');
        Schema::dropIfExists('work_order_staff');
        Schema::dropIfExists('work_order_references');
        Schema::dropIfExists('work_orders');
        Schema::dropIfExists('unit_sizes');
        Schema::dropIfExists('request_sources');
        Schema::dropIfExists('vendors');
        Schema::dropIfExists('property_staff');
        Schema::dropIfExists('properties');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('states');
        Schema::dropIfExists('management_companies');
        Schema::dropIfExists('job_statuses');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('billing_clerks');
    }
};
