<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BillingClerkController;
use App\Http\Controllers\Api\CatalogStatusController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\ManagementCompanyController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\StaffRoleController;
use App\Http\Controllers\Api\StateController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VendorController;
use App\Http\Controllers\Api\WorkOrderController;
use Illuminate\Support\Facades\Route;

Route::get('test', fn () => response('hola', 200, ['Content-Type' => 'text/plain; charset=UTF-8']));

Route::post('login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function (): void {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('/user', [UserController::class, 'show']);

    Route::get('roles', [RoleController::class, 'index']);
    Route::get('roles/{role}', [RoleController::class, 'show']);
    Route::get('/user-roles', [UserController::class, 'roles']);

    Route::middleware('admin')->group(function (): void {
        Route::apiResource('users', UserController::class)->except(['show']);
        Route::apiResource('roles', RoleController::class)->except(['index', 'show']);
    });

    Route::apiResource('management-companies', ManagementCompanyController::class);
    Route::apiResource('states', StateController::class);
    Route::apiResource('billing-clerks', BillingClerkController::class);
    Route::apiResource('staff-roles', StaffRoleController::class);
    Route::apiResource('employees', EmployeeController::class);
    Route::apiResource('properties', PropertyController::class);
    Route::apiResource('vendors', VendorController::class);

    Route::get('worksite-statuses', [CatalogStatusController::class, 'worksiteStatuses']);
    Route::post('worksite-statuses', [CatalogStatusController::class, 'storeWorksiteStatus']);
    Route::get('job-statuses', [CatalogStatusController::class, 'jobStatuses']);
    Route::post('job-statuses', [CatalogStatusController::class, 'storeJobStatus']);
    Route::get('request-sources', [CatalogStatusController::class, 'requestSources']);
    Route::post('request-sources', [CatalogStatusController::class, 'storeRequestSource']);

    Route::get('work-orders/statuses', [WorkOrderController::class, 'statuses']);
    Route::apiResource('work-orders', WorkOrderController::class);

    // Compatibilidad temporal con clientes que aún llamen /buildings
    Route::get('buildings', [PropertyController::class, 'index']);
    Route::get('buildings/{property}', [PropertyController::class, 'show']);
});
