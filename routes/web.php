<?php

use App\Http\Controllers\AuditLog\AuditLogController;
use App\Http\Controllers\PasswordPolicy\PasswordPolicyController;
use App\Http\Controllers\User\UserController;
use App\Domains\Room\Http\Controllers\RoomController;
use App\Domains\Location\Http\Controllers\LocationController;
use App\Domains\ReportType\Http\Controllers\ReportTypeController;
use App\Domains\ReportType\Http\Controllers\ReportLocationController;
use App\Domains\ReportType\Http\Controllers\ReportSectionController;
use App\Domains\ReportAssignment\Http\Controllers\ReportAssignmentController;
use App\Domains\Report\Http\Controllers\ReportClaimingController;
use App\Domains\Report\Http\Controllers\ReportDraftingController;
use App\Domains\ReportPreview\Http\Controllers\ReportPreviewController;
use App\Domains\ReportPreview\Http\Controllers\ReportPreviewStructureController;
use App\Http\Controllers\ManagerReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportArchiveController;
use App\Http\Controllers\SupervisorReportController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Redirect /dashboard berdasarkan role
Route::get('/dashboard', function () {
    $role = Auth::user()->role;
    if ($role === 'super') {
        return redirect()->route('dashboard.super-admin');
    } elseif ($role === 'admin') {
        return redirect()->route('dashboard.admin-qc');
    } elseif ($role === 'analis') {
        return redirect()->route('dashboard.analis');
    } elseif ($role === 'supervisor') {
        return redirect()->route('dashboard.supervisor');
    } elseif ($role === 'manajer') {
        return redirect()->route('dashboard.manajer');
    }

    return redirect('/');
})->middleware(['auth', 'password.check'])->name('dashboard');

// Dashboard Super Admin
Route::get('/dashboard/super-admin', function () {
    return view('pages.dashboard.super-admin');
})->middleware(['auth', 'password.check', 'role:super'])->name('dashboard.super-admin');

// Dashboard Admin QC
Route::get('/dashboard/admin-qc', function () {
    return view('pages.dashboard.admin-qc');
})->middleware(['auth', 'password.check', 'role:admin'])->name('dashboard.admin-qc');

// Manajemen Pengguna (hanya Super Admin)
Route::middleware(['auth', 'password.check', 'role:super'])
    ->prefix('dashboard/super-admin')
    ->group(function () {
        Route::resource('users', UserController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
            ->names('users');
        Route::get('super-admin/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('super-admin/settings', [PasswordPolicyController::class, 'index'])->name('settings.index');
        Route::put('super-admin/settings', [PasswordPolicyController::class, 'update'])->name('settings.update');

        // Jenis Laporan CRUD — dipindah ke admin

    });

// Tugas Pelaporan (hanya Admin QC)
Route::middleware(['auth', 'password.check', 'role:admin'])
    ->prefix('dashboard')
    ->group(function () {
        Route::resource('admin-qc/report-assignment', ReportAssignmentController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
            ->names('report-assignment');
        Route::get('admin-qc/report-assignment/{report}/preview', [ReportPreviewController::class, 'show'])->name('admin.laporan.preview');
        Route::post('admin-qc/report-assignment/{report}/sections/{sectionId}/duplicate', [ReportPreviewStructureController::class, 'duplicateSection'])->name('report-assignment.sections.duplicate');
        Route::delete('admin-qc/report-assignment/{report}/sections/{sectionId}/duplicate', [ReportPreviewStructureController::class, 'removeSection'])->name('report-assignment.sections.remove');
        // Data Master
        Route::resource('admin-qc/master/room', RoomController::class)->names('master.room');
        Route::resource('admin-qc/master/location', LocationController::class)->names('master.location');
        // Manajemen Laporan
        Route::resource('admin-qc/report-types', ReportTypeController::class)->names('report-types');
        Route::post('admin-qc/report-types/{reportType}/sections', [ReportSectionController::class, 'store'])->name('report-types.sections.store');
        Route::put('admin-qc/report-types/{reportType}/sections/{section}', [ReportSectionController::class, 'update'])->name('report-types.sections.update');
        Route::delete('admin-qc/report-types/{reportType}/sections/{section}', [ReportSectionController::class, 'destroy'])->name('report-types.sections.destroy');
        Route::post('admin-qc/report-types/{reportType}/sections/{section}/locations', [ReportLocationController::class, 'store'])->name('report-types.sections.locations.store');
        Route::delete('admin-qc/report-types/{reportType}/sections/{section}/locations/{location}', [ReportLocationController::class, 'destroy'])->name('report-types.sections.locations.destroy');
    });

// Dashboard & Laporan Analis
Route::middleware(['auth', 'password.check', 'role:analis'])
    ->prefix('dashboard')
    ->group(function () {
        Route::get('analyst', function () {
            return view('pages.dashboard.analis');
        })->name('dashboard.analis');

        Route::get('analyst/reports', [ReportClaimingController::class, 'index'])->name('laporan.index');
        Route::get('analyst/reports/{report}/fill', [ReportClaimingController::class, 'isi'])->name('laporan.isi');
        Route::get('analyst/reports/{report}/preview', [ReportPreviewController::class, 'show'])->name('laporan.lihat');
        Route::post('analyst/reports/{report}/save', [ReportDraftingController::class, 'save'])->name('laporan.save');
        Route::post('analyst/reports/verify-password', [ReportDraftingController::class, 'verifyPassword'])->name('laporan.verify-password');
    });

// Dashboard & Laporan Masuk Supervisor
Route::middleware(['auth', 'password.check', 'role:supervisor'])
    ->prefix('dashboard')
    ->group(function () {
        Route::get('supervisor', [SupervisorReportController::class, 'dashboard'])->name('dashboard.supervisor');
        Route::get('supervisor/incoming-reports', [SupervisorReportController::class, 'laporanMasuk'])->name('supervisor.laporan-masuk');
        Route::get('supervisor/ongoing-reports', [SupervisorReportController::class, 'laporanSedangDikerjakan'])->name('supervisor.laporan-sedang-dikerjakan');
        Route::get('supervisor/reports/{report}/preview', [ReportPreviewController::class, 'show'])->name('supervisor.laporan.preview');
        Route::get('supervisor/reports/{report}', [SupervisorReportController::class, 'show'])->name('supervisor.laporan.show');
        Route::get('supervisor/reports/{report}/print', [SupervisorReportController::class, 'cetak'])->name('supervisor.laporan.cetak');
        Route::post('supervisor/reports/{report}/save', [SupervisorReportController::class, 'save'])->name('supervisor.laporan.save');
        Route::post('supervisor/reports/{report}/approve', [SupervisorReportController::class, 'approve'])->name('supervisor.laporan.approve');
        Route::post('supervisor/reports/{report}/return', [SupervisorReportController::class, 'returnReport'])->name('supervisor.laporan.return');
    });

// Dashboard & Laporan Masuk Manajer
Route::middleware(['auth', 'password.check', 'role:manajer'])
    ->prefix('dashboard')
    ->group(function () {
        Route::get('manager', [ManagerReportController::class, 'dashboard'])->name('dashboard.manajer');
        Route::get('manager/incoming-reports', [ManagerReportController::class, 'laporanMasuk'])->name('manajer.laporan-masuk');
        Route::get('manager/ongoing-reports', [ManagerReportController::class, 'laporanSedangDikerjakan'])->name('manajer.laporan-sedang-dikerjakan');
        Route::get('manager/reports/{report}/preview', [ReportPreviewController::class, 'show'])->name('manajer.laporan.preview');
        Route::get('manager/reports/{report}', [ManagerReportController::class, 'show'])->name('manajer.laporan.show');
        Route::get('manager/reports/{report}/print', [ManagerReportController::class, 'cetak'])->name('manajer.laporan.cetak');
        Route::post('manager/reports/{report}/save', [ManagerReportController::class, 'save'])->name('manajer.laporan.save');
        Route::post('manager/reports/{report}/approve', [ManagerReportController::class, 'approve'])->name('manajer.laporan.approve');
        Route::post('manager/reports/{report}/return', [ManagerReportController::class, 'returnReport'])->name('manajer.laporan.return');
    });

// Arsip Laporan (analis, admin, supervisor, manajer)
Route::middleware(['auth', 'password.check', 'role:analis,admin,supervisor,manajer'])
    ->prefix('dashboard/archive')
    ->group(function () {
        Route::get('/', [ReportArchiveController::class, 'index'])->name('arsip-laporan.index');
        Route::get('/{report}', [ReportArchiveController::class, 'show'])->name('arsip-laporan.show');
    });

Route::middleware(['auth', 'password.check'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
