<?php

use App\Http\Controllers\Api\AgendaController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BuildingController;
use App\Http\Controllers\Api\ManagementCompanyController;
use App\Http\Controllers\Api\PendingJobController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\ScheduleDayController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\SubcontractorController;
use App\Http\Controllers\Api\SubcontractorEmployeeController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WorkOrderController;
use App\Http\Controllers\Api\ZoneController;
use Illuminate\Support\Facades\Route;

Route::get('test', fn () => response('hola', 200, ['Content-Type' => 'text/plain; charset=UTF-8']));

Route::post('login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function (): void {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('/user', [UserController::class, 'show']);

    // Roles legibles por cualquier usuario autenticado (para poblar dropdowns)
    Route::get('roles', [RoleController::class, 'index']);
    Route::get('roles/{role}', [RoleController::class, 'show']);
    Route::get('/user-roles', [UserController::class, 'roles']);

    // Administración: creación/edición/borrado (solo admin)
    Route::middleware('admin')->group(function (): void {
        Route::apiResource('users', UserController::class)->except(['show']);
        Route::apiResource('roles', RoleController::class)->except(['index', 'show']);
    });

    // Catálogos maestros
    Route::apiResource('management-companies', ManagementCompanyController::class);
    Route::apiResource('zones', ZoneController::class);

    Route::get('staff/roles', [StaffController::class, 'roles']);
    Route::apiResource('staff', StaffController::class)->parameters(['staff' => 'staff']);

    Route::apiResource('buildings', BuildingController::class);

    // Subcontractors + empleados (routes anidadas)
    Route::apiResource('subcontractors', SubcontractorController::class);
    Route::apiResource('subcontractors.employees', SubcontractorEmployeeController::class)
        ->shallow();

    // Agenda / operación
    // Schedule Days + generación de agenda: solo admin o scheduler_coordinator
    Route::middleware('schedule.manage')->group(function (): void {
        Route::apiResource('schedule-days', ScheduleDayController::class)
            ->parameters(['schedule-days' => 'scheduleDay']);

        Route::get('agenda/{day}', [AgendaController::class, 'show']);
        Route::get('agenda/{day}/export/xlsx', [AgendaController::class, 'exportXlsx']);
        Route::get('agenda/{day}/export/pdf', [AgendaController::class, 'exportPdf']);
    });

    Route::get('work-orders/statuses', [WorkOrderController::class, 'statuses']);
    Route::apiResource('work-orders', WorkOrderController::class);

    Route::apiResource('pending-jobs', PendingJobController::class);
});
