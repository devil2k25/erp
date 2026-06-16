<?php

use App\Http\Controllers\Api\V1\Admin\AdminAuthController;
use App\Http\Controllers\Api\V1\Admin\AdminOrganizationController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Dashboard\DashboardController;
use App\Http\Controllers\Api\V1\Inventory\GrnController;
use App\Http\Controllers\Api\V1\Inventory\InventoryItemController;
use App\Http\Controllers\Api\V1\Inventory\InventoryTransactionController;
use App\Http\Controllers\Api\V1\MasterData\BomController;
use App\Http\Controllers\Api\V1\MasterData\CategoryController;
use App\Http\Controllers\Api\V1\MasterData\CustomerController;
use App\Http\Controllers\Api\V1\MasterData\DepartmentController;
use App\Http\Controllers\Api\V1\MasterData\MachineController;
use App\Http\Controllers\Api\V1\MasterData\ProductController;
use App\Http\Controllers\Api\V1\MasterData\ProductionLineController;
use App\Http\Controllers\Api\V1\MasterData\ShiftController;
use App\Http\Controllers\Api\V1\MasterData\UnitController;
use App\Http\Controllers\Api\V1\MasterData\VendorController;
use App\Http\Controllers\Api\V1\MasterData\WarehouseController;
use App\Http\Controllers\Api\V1\Production\ProductionEntryController;
use App\Http\Controllers\Api\V1\Production\ProductionPlanController;
use App\Http\Controllers\Api\V1\Worker\AttendanceController;
use App\Http\Controllers\Api\V1\Worker\WorkerController;
use Illuminate\Support\Facades\Route;

// ─── Public ──────────────────────────────────────────────────────────────────
Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
});

// ─── Admin panel (super admin, no tenant context) ────────────────────────────
Route::prefix('v1/admin')->group(function () {
    Route::post('/auth/login', [AdminAuthController::class, 'login']);

    Route::middleware('auth:admin')->group(function () {
        Route::get('/auth/me', [AdminAuthController::class, 'me']);
        Route::post('/auth/logout', [AdminAuthController::class, 'logout']);

        Route::get('/organizations', [AdminOrganizationController::class, 'index']);
        Route::post('/organizations', [AdminOrganizationController::class, 'store']);
        Route::get('/organizations/{organization}', [AdminOrganizationController::class, 'show']);
        Route::patch('/organizations/{organization}/status', [AdminOrganizationController::class, 'updateStatus']);
    });
});

// ─── Tenant API (tenant resolved from bearer token, then sanctum auth) ────────
Route::prefix('v1')
    ->middleware(['tenant', 'auth:sanctum', 'org.active'])
    ->group(function () {
        // Auth
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
        });

        // Master Data
        Route::prefix('master')->group(function () {
            Route::apiResource('products', ProductController::class);
            Route::apiResource('categories', CategoryController::class);
            Route::apiResource('units', UnitController::class);
            Route::apiResource('warehouses', WarehouseController::class);
            Route::apiResource('production-lines', ProductionLineController::class);
            Route::apiResource('machines', MachineController::class);
            Route::apiResource('vendors', VendorController::class);
            Route::apiResource('customers', CustomerController::class);
            Route::apiResource('departments', DepartmentController::class);
            Route::apiResource('shifts', ShiftController::class);
            Route::apiResource('bom', BomController::class);
            Route::get('products/{product}/bom', [BomController::class, 'forProduct']);
        });

        // Inventory
        Route::prefix('inventory')->group(function () {
            Route::get('stock-summary', [InventoryItemController::class, 'stockSummary']);
            Route::get('low-stock-alerts', [InventoryItemController::class, 'lowStockAlerts']);
            Route::apiResource('items', InventoryItemController::class)->only(['index', 'show']);
            Route::apiResource('transactions', InventoryTransactionController::class)->only(['index', 'store', 'show']);
            Route::apiResource('grn', GrnController::class);
            Route::post('grn/{grn}/approve', [GrnController::class, 'approve']);
        });

        // Production
        Route::prefix('production')->group(function () {
            Route::get('oee-summary', [ProductionEntryController::class, 'oeeSummary']);
            Route::apiResource('plans', ProductionPlanController::class);
            Route::post('plans/{plan}/approve', [ProductionPlanController::class, 'approve']);
            Route::post('plans/{plan}/start', [ProductionPlanController::class, 'start']);
            Route::apiResource('entries', ProductionEntryController::class);
        });

        // Workers
        Route::prefix('workers')->group(function () {
            Route::apiResource('/', WorkerController::class)->parameters(['' => 'worker']);
            Route::get('attendance/report', [AttendanceController::class, 'report']);
            Route::post('attendance/bulk', [AttendanceController::class, 'bulkMark']);
            Route::apiResource('attendance', AttendanceController::class);
        });

        // Dashboard
        Route::prefix('dashboard')->group(function () {
            Route::get('owner', [DashboardController::class, 'owner']);
            Route::get('production', [DashboardController::class, 'production']);
            Route::get('inventory', [DashboardController::class, 'inventory']);
        });
    });
