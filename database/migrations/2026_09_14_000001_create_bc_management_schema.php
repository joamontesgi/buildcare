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
        });

        Schema::create('employees', function (Blueprint $table): void {
            $table->id();
            $table->string('first_name', 100);
            $table->string('last_name', 100)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
        });

        Schema::create('job_statuses', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100)->unique();
        });

        Schema::create('management_companies', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 150)->unique();
        });

        Schema::create('states', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name', 100);
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100)->unique();
        });

        Schema::create('properties', function (Blueprint $table): void {
            $table->id();
            $table->string('building_name', 200);
            $table->string('address', 300);
            $table->foreignId('state_id')->constrained('states');
            $table->foreignId('management_id')->constrained('management_companies');
            $table->foreignId('billing_clerk_id')->nullable()->constrained('billing_clerks');
        });

        Schema::create('property_staff', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->constrained('properties');
            $table->foreignId('employee_id')->constrained('employees');
            $table->foreignId('role_id')->constrained('roles');
            $table->unique(['property_id', 'employee_id', 'role_id'], 'uq_property_employee_role');
        });

        Schema::create('vendors', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 200);
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
        });

        Schema::create('request_sources', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100)->unique();
        });

        Schema::create('worksite_statuses', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100)->unique();
        });

        Schema::create('work_orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->constrained('properties');
            $table->string('unit_area', 100)->nullable();
            $table->string('size', 100)->nullable();
            $table->foreignId('worksite_status_id')->nullable()->constrained('worksite_statuses');
            $table->foreignId('job_status_id')->nullable()->constrained('job_statuses');
            $table->text('job_description')->nullable();
            $table->string('bc_work_order', 100)->nullable();
            $table->string('bc_estimate', 100)->nullable();
            $table->text('extras')->nullable();
            $table->text('special_notes_sequence')->nullable();
            $table->foreignId('request_source_id')->nullable()->constrained('request_sources');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('work_order_references', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders');
            $table->string('reference_type', 30);
            $table->string('reference_number', 100)->nullable();
        });

        Schema::create('work_order_staff', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders');
            $table->foreignId('employee_id')->constrained('employees');
            $table->foreignId('role_id')->constrained('roles');
            $table->unique(['work_order_id', 'employee_id', 'role_id'], 'uq_work_order_employee_role');
        });

        Schema::create('work_order_vendors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders');
            $table->foreignId('vendor_id')->constrained('vendors');
            $table->unique(['work_order_id', 'vendor_id'], 'uq_work_order_vendor');
        });

        Schema::create('vendor_daily_status_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders');
            $table->foreignId('vendor_id')->nullable()->constrained('vendors');
            $table->date('report_date');
            $table->string('status', 100)->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_daily_status_reports');
        Schema::dropIfExists('work_order_vendors');
        Schema::dropIfExists('work_order_staff');
        Schema::dropIfExists('work_order_references');
        Schema::dropIfExists('work_orders');
        Schema::dropIfExists('worksite_statuses');
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
