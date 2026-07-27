<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\MaterialRequisitionController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Main Application
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Admin Only System Routes
    Route::middleware(['role:admin'])->group(function () {
        Route::delete('assets/bulk-delete', [AssetController::class, 'bulkDelete'])->name('assets.bulk-delete');
        Route::resource('users', \App\Http\Controllers\UserController::class);
    });

    // Admin & Procurement Officer Action Routes
    Route::middleware(['role:admin|procurement'])->group(function () {
        Route::get('assets/export', [AssetController::class, 'export'])->name('assets.export');
        Route::get('assets/download-template', [AssetController::class, 'downloadTemplate'])->name('assets.download-template');
        Route::post('assets/import/preview', [AssetController::class, 'importPreview'])->name('assets.import.preview');
        Route::post('assets/import/process-json', [AssetController::class, 'importProcessJson'])->name('assets.import.process-json');
        Route::get('assets/batch-print', [AssetController::class, 'batchPrint'])->name('assets.batch-print');
        Route::post('assets/print-labels', [AssetController::class, 'printLabels'])->name('assets.print-labels');
        Route::get('assets/{asset}/print', [AssetController::class, 'printLabel'])->name('assets.print');
        
        Route::post('requisitions/items/{item}/approve', [MaterialRequisitionController::class, 'approveItem'])->name('requisitions.approve-item');
        Route::post('requisitions/items/{item}/reject', [MaterialRequisitionController::class, 'rejectItem'])->name('requisitions.reject-item');

        // Material Stock Cards Actions (บันทึกรับเข้า & ตั้งค่าอย่างต่ำ/สูง)
        Route::post('stock-cards/{material}/stock-in', [\App\Http\Controllers\MaterialStockCardController::class, 'storeStockIn'])->name('stock-cards.stock-in');
        Route::put('stock-cards/{material}/settings', [\App\Http\Controllers\MaterialStockCardController::class, 'updateCardSettings'])->name('stock-cards.update-settings');

        Route::resource('assets', AssetController::class)->except(['index', 'show']);
        Route::resource('materials', MaterialController::class)->except(['index', 'show']);
        Route::resource('categories', CategoryController::class)->except(['index']);
        Route::resource('locations', LocationController::class)->except(['index']);
        Route::resource('departments', DepartmentController::class)->except(['index']);
        Route::resource('vendors', VendorController::class)->except(['index']);
    });

    // Shared Read & Action Routes for All Roles (admin, procurement, user)
    Route::middleware(['role:admin|procurement|user'])->group(function () {
        Route::get('assets', [AssetController::class, 'index'])->name('assets.index');
        Route::get('assets/{asset}', [AssetController::class, 'show'])->name('assets.show');
        Route::post('assets/{asset}/status', [AssetController::class, 'updateStatus'])->name('assets.update-status');
        
        Route::get('materials', [MaterialController::class, 'index'])->name('materials.index');
        Route::get('materials/{material}', [MaterialController::class, 'show'])->name('materials.show');
        
        // Material Stock Cards View & Print for All Roles
        Route::get('stock-cards', [\App\Http\Controllers\MaterialStockCardController::class, 'index'])->name('stock-cards.index');
        Route::get('stock-cards/{material}', [\App\Http\Controllers\MaterialStockCardController::class, 'show'])->name('stock-cards.show');
        Route::get('stock-cards/{material}/print', [\App\Http\Controllers\MaterialStockCardController::class, 'print'])->name('stock-cards.print');

        Route::get('requisitions', [MaterialRequisitionController::class, 'index'])->name('requisitions.index');
        Route::get('requisitions/export-csv', [MaterialRequisitionController::class, 'exportCsv'])->name('requisitions.export-csv');
        Route::post('requisitions', [MaterialRequisitionController::class, 'store'])->name('requisitions.store');
        Route::get('requisitions/{requisition}', [MaterialRequisitionController::class, 'show'])->name('requisitions.show');
        Route::get('requisitions/{requisition}/print', [MaterialRequisitionController::class, 'print'])->name('requisitions.print');

        Route::get('notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

        Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('locations', [LocationController::class, 'index'])->name('locations.index');
        Route::get('departments', [DepartmentController::class, 'index'])->name('departments.index');
        Route::get('vendors', [VendorController::class, 'index'])->name('vendors.index');
        
        Route::post('transactions', [TransactionController::class, 'store'])->name('transactions.store');
    });
});
