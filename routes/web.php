<?php

use App\Http\Controllers\AuditLog\AuditLogController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\PasswordPolicy\PasswordPolicyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Location\LocationController;
use App\Http\Controllers\Report\ReportClaimingController;
use App\Http\Controllers\Report\ReportDraftingController;
use App\Http\Controllers\ReportAssignment\ReportAssignmentController;
use App\Http\Controllers\ReportPreview\ReportPreviewController;
use App\Http\Controllers\ReportPreview\ReportPreviewStructureController;
use App\Http\Controllers\ReportType\ReportLocationController;
use App\Http\Controllers\ReportType\ReportSectionController;
use App\Http\Controllers\ReportType\ReportTypeController;
use App\Http\Controllers\Room\RoomController;
use App\Http\Controllers\ManagerReportController;
use App\Http\Controllers\ReportArchiveController;
use App\Http\Controllers\SupervisorReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'password.check'])->group(function () {
    // Single dashboard route — DashboardService resolves the right view per role.
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile (all authenticated users)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Super Admin
    Route::middleware('role:super')->group(function () {
        Route::resource('users', UserController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
            ->names('users');
        Route::get('/settings/password', [PasswordPolicyController::class, 'index'])->name('settings.index');
        Route::put('/settings/password', [PasswordPolicyController::class, 'update'])->name('settings.update');
    });

    // Admin QC
    Route::middleware('role:admin')->group(function () {
        Route::resource('rooms', RoomController::class)->names('master.room')->parameters(['rooms' => 'room']);
        Route::resource('locations', LocationController::class)->names('master.location')->parameters(['locations' => 'location']);

        Route::resource('report-types', ReportTypeController::class)->names('report-types');
        Route::post('/report-types/{reportType}/sections', [ReportSectionController::class, 'store'])->name('report-types.sections.store');
        Route::put('/report-types/{reportType}/sections/{section}', [ReportSectionController::class, 'update'])->name('report-types.sections.update');
        Route::delete('/report-types/{reportType}/sections/{section}', [ReportSectionController::class, 'destroy'])->name('report-types.sections.destroy');
        Route::post('/report-types/{reportType}/sections/{section}/locations', [ReportLocationController::class, 'store'])->name('report-types.sections.locations.store');
        Route::delete('/report-types/{reportType}/sections/{section}/locations/{location}', [ReportLocationController::class, 'destroy'])->name('report-types.sections.locations.destroy');

        Route::resource('report-assignments', ReportAssignmentController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
            ->names('report-assignment')
            ->parameters(['report-assignments' => 'report']);
        Route::get('/report-assignments/{report}/preview', [ReportPreviewController::class, 'show'])->name('admin.reports.preview');
        Route::post('/report-assignments/{report}/sections/{sectionId}/duplicate', [ReportPreviewStructureController::class, 'duplicateSection'])->name('report-assignment.sections.duplicate');
        Route::delete('/report-assignments/{report}/sections/{sectionId}/duplicate', [ReportPreviewStructureController::class, 'removeSection'])->name('report-assignment.sections.remove');
    });

    // Analyst
    Route::middleware('role:analyst')->group(function () {
        Route::get('/reports', [ReportClaimingController::class, 'index'])->name('reports.index');
        Route::get('/reports/{report}/fill', [ReportClaimingController::class, 'fill'])->name('reports.fill');
        Route::get('/reports/{report}/preview', [ReportPreviewController::class, 'show'])->name('reports.preview');
        Route::post('/reports/{report}/save', [ReportDraftingController::class, 'save'])->name('reports.save');
        Route::post('/reports/verify-password', [ReportDraftingController::class, 'verifyPassword'])->name('reports.verify-password');
    });

    // Supervisor
    Route::middleware('role:supervisor')->prefix('supervisor')->name('supervisor.')->group(function () {
        Route::get('/incoming-reports', [SupervisorReportController::class, 'incomingReports'])->name('incoming-reports');
        Route::get('/ongoing-reports', [SupervisorReportController::class, 'ongoingReports'])->name('ongoing-reports');
        Route::get('/reports/{report}/preview', [ReportPreviewController::class, 'show'])->name('reports.preview');
        Route::get('/reports/{report}', [SupervisorReportController::class, 'show'])->name('reports.show');
        Route::get('/reports/{report}/print', [SupervisorReportController::class, 'print'])->name('reports.print');
        Route::post('/reports/{report}/save', [SupervisorReportController::class, 'save'])->name('reports.save');
        Route::post('/reports/{report}/approve', [SupervisorReportController::class, 'approve'])->name('reports.approve');
        Route::post('/reports/{report}/return', [SupervisorReportController::class, 'returnReport'])->name('reports.return');
    });

    // Manager
    Route::middleware('role:manager')->prefix('manager')->name('manager.')->group(function () {
        Route::get('/incoming-reports', [ManagerReportController::class, 'incomingReports'])->name('incoming-reports');
        Route::get('/ongoing-reports', [ManagerReportController::class, 'ongoingReports'])->name('ongoing-reports');
        Route::get('/reports/{report}/preview', [ReportPreviewController::class, 'show'])->name('reports.preview');
        Route::get('/reports/{report}', [ManagerReportController::class, 'show'])->name('reports.show');
        Route::get('/reports/{report}/print', [ManagerReportController::class, 'print'])->name('reports.print');
        Route::post('/reports/{report}/save', [ManagerReportController::class, 'save'])->name('reports.save');
        Route::post('/reports/{report}/approve', [ManagerReportController::class, 'approve'])->name('reports.approve');
        Route::post('/reports/{report}/return', [ManagerReportController::class, 'returnReport'])->name('reports.return');
    });

    // Report archive (analyst, admin, supervisor, manager)
    Route::middleware('role:analyst,admin,supervisor,manager')
        ->prefix('archive')
        ->name('report-archive.')
        ->group(function () {
            Route::get('/', [ReportArchiveController::class, 'index'])->name('index');
            Route::get('/{report}', [ReportArchiveController::class, 'show'])->name('show');
        });
        
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

});

require __DIR__.'/auth.php';
