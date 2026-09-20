<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Esquema alineado 1:1 con buildcare_modelo_relacional.mmd + ajustes de negocio:
 *
 *   MANAGEMENT_COMPANIES  -< BUILDINGS
 *   ZONES                 -< BUILDINGS
 *   STAFF                 -< BUILDINGS (bc_supervisor)
 *   STAFF                 -< SCHEDULE_DAYS (supervisor on call)
 *   SCHEDULE_DAYS         -< WORK_ORDERS
 *   BUILDINGS             -< WORK_ORDERS
 *   SUBCONTRACTORS        -< WORK_ORDERS   (antes "crews")
 *   SUBCONTRACTORS        -< SUBCONTRACTOR_EMPLOYEES
 *   WORK_ORDERS           -o PENDING_JOBS
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('management_companies', function (Blueprint $table): void {
            $table->id('management_id');
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('zones', function (Blueprint $table): void {
            $table->id('zone_id');
            $table->string('zone_name');
            $table->string('state', 32)->nullable();
            $table->timestamps();

            $table->unique(['zone_name', 'state']);
        });

        Schema::create('staff', function (Blueprint $table): void {
            $table->id('staff_id');
            $table->string('name');
            $table->string('role')->nullable();
            $table->string('phone', 64)->nullable();
            $table->string('email')->nullable()->unique();
            $table->timestamps();
        });

        Schema::create('buildings', function (Blueprint $table): void {
            $table->id('building_id');
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('state', 32)->nullable();

            $table->unsignedBigInteger('management_id')->nullable();
            $table->unsignedBigInteger('zone_id')->nullable();
            $table->unsignedBigInteger('bc_supervisor_id')->nullable();

            $table->string('billing_clerk_code', 64)->nullable();
            $table->string('travel_expense')->nullable();
            $table->text('paint_specs')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('management_id')
                ->references('management_id')->on('management_companies')
                ->nullOnDelete();

            $table->foreign('zone_id')
                ->references('zone_id')->on('zones')
                ->nullOnDelete();

            $table->foreign('bc_supervisor_id')
                ->references('staff_id')->on('staff')
                ->nullOnDelete();

            $table->index('state');
        });

        // Subcontractors (antes "crews"). Nombre de la empresa + teléfono + capacidades.
        Schema::create('subcontractors', function (Blueprint $table): void {
            $table->id('subcontractor_id');
            $table->string('company_name');
            $table->string('contact_name')->nullable();
            $table->string('phone', 64)->nullable();
            $table->text('capabilities')->nullable();
            $table->timestamps();

            $table->unique('company_name');
        });

        // Empleados del subcontratista: nombre, teléfono, rol.
        Schema::create('subcontractor_employees', function (Blueprint $table): void {
            $table->id('employee_id');
            $table->unsignedBigInteger('subcontractor_id');
            $table->string('name');
            $table->string('phone', 64)->nullable();
            $table->string('role', 64)->nullable();
            $table->timestamps();

            $table->foreign('subcontractor_id')
                ->references('subcontractor_id')->on('subcontractors')
                ->cascadeOnDelete();
        });

        Schema::create('schedule_days', function (Blueprint $table): void {
            $table->date('day_id')->primary();
            $table->string('day_of_week', 20)->nullable();
            $table->unsignedBigInteger('supervisor_on_call_id')->nullable();

            // Cabecera del día (mismo layout que el Excel).
            $table->text('default_crews_note')->nullable();      // "CREWS ASSIGNED BY DEFAULT ON CALL"
            $table->text('crews_confirmed_note')->nullable();    // "CREWS CONFIRMED BY THE ON CALL"
            $table->text('bc_off_note')->nullable();             // "BC MEMBERS OFF/VACATIONS"
            $table->text('crew_off_note')->nullable();           // "CREW MEMBERS OFF/VACATIONS"

            $table->timestamps();

            $table->foreign('supervisor_on_call_id')
                ->references('staff_id')->on('staff')
                ->nullOnDelete();
        });

        Schema::create('work_orders', function (Blueprint $table): void {
            $table->id('job_id');
            $table->date('day_id');
            $table->unsignedBigInteger('building_id')->nullable();
            $table->unsignedBigInteger('subcontractor_id')->nullable();

            $table->string('unit_area')->nullable();
            $table->string('size', 64)->nullable();
            $table->string('worksite_status', 64)->nullable();
            $table->text('job_description')->nullable();
            $table->string('request_po_wtn_wo')->nullable();
            // BC work order y BC estimate son campos diferentes en operación.
            $table->string('bc_work_order')->nullable();
            $table->string('bc_estimate_ref')->nullable();
            $table->text('extras')->nullable();
            $table->text('special_notes')->nullable();
            $table->text('vendor_status_report')->nullable();
            $table->timestamps();

            $table->foreign('day_id')
                ->references('day_id')->on('schedule_days')
                ->cascadeOnDelete();

            $table->foreign('building_id')
                ->references('building_id')->on('buildings')
                ->nullOnDelete();

            $table->foreign('subcontractor_id')
                ->references('subcontractor_id')->on('subcontractors')
                ->nullOnDelete();

            $table->index('worksite_status');
        });

        Schema::create('pending_jobs', function (Blueprint $table): void {
            $table->id('pending_id');
            $table->unsignedBigInteger('job_id')->unique();
            $table->text('reason_pending')->nullable();
            $table->timestamps();

            $table->foreign('job_id')
                ->references('job_id')->on('work_orders')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_jobs');
        Schema::dropIfExists('work_orders');
        Schema::dropIfExists('schedule_days');
        Schema::dropIfExists('subcontractor_employees');
        Schema::dropIfExists('subcontractors');
        Schema::dropIfExists('buildings');
        Schema::dropIfExists('staff');
        Schema::dropIfExists('zones');
        Schema::dropIfExists('management_companies');
    }
};
