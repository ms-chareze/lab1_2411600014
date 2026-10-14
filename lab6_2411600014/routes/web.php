<?php

use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnrollmentTransactionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UnitAdjustmentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Student Grade Portal CRUD Routes
    Route::resource('students', StudentController::class);

    // ---- Lab 6: Real-Time Unit (Inventory) Tracking ----------------------

    // Transaction log (delete is admin-only, enforced by EnrollmentTransactionPolicy)
    Route::resource('transactions', EnrollmentTransactionController::class)
        ->only(['index', 'create', 'store', 'show', 'destroy']);

    // Stock adjustments (admin-only)
    Route::get('/adjustments/create', [UnitAdjustmentController::class, 'create'])->name('adjustments.create');
    Route::post('/adjustments', [UnitAdjustmentController::class, 'store'])->name('adjustments.store');

    // Reports
    Route::get('/reports/movement', [ReportController::class, 'movement'])->name('reports.movement');
    Route::get('/reports/low-load', [ReportController::class, 'lowLoad'])->name('reports.low-load');
    Route::get('/reports/valuation', [ReportController::class, 'valuation'])->name('reports.valuation');

    // Notifications (bell dropdown)
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');

    // JSON API for AJAX polling. Lives in web.php so it shares the session
    // login (no Sanctum/token setup needed): GET /api/...
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/dashboard/stats', [DashboardApiController::class, 'stats'])->name('dashboard.stats');
        Route::get('/students/low-load', [DashboardApiController::class, 'lowLoad'])->name('students.low-load');
        Route::get('/students/{student}/units', [DashboardApiController::class, 'studentUnits'])->name('students.units');
        Route::get('/transactions/recent', [DashboardApiController::class, 'recentTransactions'])->name('transactions.recent');
    });
});

require __DIR__.'/auth.php';
