<?php

use App\Domains\User\Http\Controllers\UserController;
use App\Domains\Room\Http\Controllers\RoomController;
use App\Domains\Location\Http\Controllers\LocationController;
use App\Domains\ReportType\Http\Controllers\ReportTypeController;
use App\Domains\ReportType\Http\Controllers\ReportLocationController;
use App\Domains\ReportType\Http\Controllers\ReportSectionController;
use App\Domains\ReportAssignment\Http\Controllers\ReportAssignmentController;
use App\Domains\AnalystReport\ReportClaiming\Http\Controllers\ReportClaimingController;
use App\Domains\AnalystReport\ReportDrafting\Http\Controllers\ReportDraftingController;
use App\Domains\ReportPreview\Http\Controllers\ReportPreviewController;
use App\Domains\ReportPreview\Http\Controllers\ReportPreviewStructureController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\ManagerReportController;
use App\Http\Controllers\PasswordSettingController;
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
    ->prefix('dashboard')
    ->group(function () {
        Route::resource('users', UserController::class)
            ->only(['index', 'create', 'store', 'destroy'])
            ->names('users');
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('settings', [PasswordSettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [PasswordSettingController::class, 'update'])->name('settings.update');

        // Jenis Laporan CRUD — dipindah ke admin

    });

// Tugas Pelaporan (hanya Admin QC)
Route::middleware(['auth', 'password.check', 'role:admin'])
    ->prefix('dashboard')
    ->group(function () {
        Route::resource('report-assignment', ReportAssignmentController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
            ->names('report-assignment');
        Route::get('report-assignment/{report}/preview', [ReportPreviewController::class, 'show'])->name('admin.laporan.preview');
        Route::post('report-assignment/{report}/personnel-page', [ReportPreviewStructureController::class, 'personnelPage'])->name('admin.laporan.personnel-page');
        Route::post('report-assignment/{report}/sections/{sectionId}/duplicate', [ReportPreviewStructureController::class, 'duplicateSection'])->name('report-assignment.sections.duplicate');
        Route::delete('report-assignment/{report}/sections/{sectionId}/duplicate', [ReportPreviewStructureController::class, 'removeSection'])->name('report-assignment.sections.remove');
        // Data Master
        Route::resource('master/room', RoomController::class)->names('master.room');
        Route::resource('master/location', LocationController::class)->names('master.location');
        // Manajemen Laporan
        Route::resource('report-types', ReportTypeController::class)->names('report-types');
        Route::post('report-types/{reportType}/sections', [ReportSectionController::class, 'store'])->name('report-types.sections.store');
        Route::put('report-types/{reportType}/sections/{section}', [ReportSectionController::class, 'update'])->name('report-types.sections.update');
        Route::delete('report-types/{reportType}/sections/{section}', [ReportSectionController::class, 'destroy'])->name('report-types.sections.destroy');
        Route::post('report-types/{reportType}/sections/{section}/locations', [ReportLocationController::class, 'store'])->name('report-types.sections.locations.store');
        Route::delete('report-types/{reportType}/sections/{section}/locations/{location}', [ReportLocationController::class, 'destroy'])->name('report-types.sections.locations.destroy');
    });

// Dashboard & Laporan Analis
Route::middleware(['auth', 'password.check', 'role:analis'])
    ->prefix('dashboard')
    ->group(function () {
        Route::get('analis', function () {
            return view('pages.dashboard.analis');
        })->name('dashboard.analis');

        Route::get('laporan', [ReportClaimingController::class, 'index'])->name('laporan.index');
        Route::get('laporan/{report}/isi', [ReportClaimingController::class, 'isi'])->name('laporan.isi');
        Route::get('laporan/{report}/lihat', [ReportPreviewController::class, 'show'])->name('laporan.lihat');
        Route::post('laporan/{report}/save', [ReportDraftingController::class, 'save'])->name('laporan.save');
        Route::post('laporan/verify-password', [ReportDraftingController::class, 'verifyPassword'])->name('laporan.verify-password');
    });

// Dashboard & Laporan Masuk Supervisor
Route::middleware(['auth', 'password.check', 'role:supervisor'])
    ->prefix('dashboard')
    ->group(function () {
        Route::get('supervisor', [SupervisorReportController::class, 'dashboard'])->name('dashboard.supervisor');
        Route::get('supervisor/laporan-masuk', [SupervisorReportController::class, 'laporanMasuk'])->name('supervisor.laporan-masuk');
        Route::get('supervisor/laporan-sedang-dikerjakan', [SupervisorReportController::class, 'laporanSedangDikerjakan'])->name('supervisor.laporan-sedang-dikerjakan');
        Route::get('supervisor/laporan/{report}/preview', [ReportPreviewController::class, 'show'])->name('supervisor.laporan.preview');
        Route::get('supervisor/laporan/{report}', [SupervisorReportController::class, 'show'])->name('supervisor.laporan.show');
        Route::get('supervisor/laporan/{report}/cetak', [SupervisorReportController::class, 'cetak'])->name('supervisor.laporan.cetak');
        Route::post('supervisor/laporan/{report}/save', [SupervisorReportController::class, 'save'])->name('supervisor.laporan.save');
        Route::post('supervisor/laporan/{report}/approve', [SupervisorReportController::class, 'approve'])->name('supervisor.laporan.approve');
        Route::post('supervisor/laporan/{report}/return', [SupervisorReportController::class, 'returnReport'])->name('supervisor.laporan.return');
    });

// Dashboard & Laporan Masuk Manajer
Route::middleware(['auth', 'password.check', 'role:manajer'])
    ->prefix('dashboard')
    ->group(function () {
        Route::get('manajer', [ManagerReportController::class, 'dashboard'])->name('dashboard.manajer');
        Route::get('manajer/laporan-masuk', [ManagerReportController::class, 'laporanMasuk'])->name('manajer.laporan-masuk');
        Route::get('manajer/laporan-sedang-dikerjakan', [ManagerReportController::class, 'laporanSedangDikerjakan'])->name('manajer.laporan-sedang-dikerjakan');
        Route::get('manajer/laporan/{report}/preview', [ReportPreviewController::class, 'show'])->name('manajer.laporan.preview');
        Route::get('manajer/laporan/{report}', [ManagerReportController::class, 'show'])->name('manajer.laporan.show');
        Route::get('manajer/laporan/{report}/cetak', [ManagerReportController::class, 'cetak'])->name('manajer.laporan.cetak');
        Route::post('manajer/laporan/{report}/save', [ManagerReportController::class, 'save'])->name('manajer.laporan.save');
        Route::post('manajer/laporan/{report}/approve', [ManagerReportController::class, 'approve'])->name('manajer.laporan.approve');
        Route::post('manajer/laporan/{report}/return', [ManagerReportController::class, 'returnReport'])->name('manajer.laporan.return');
    });

// Arsip Laporan (analis, admin, supervisor, manajer)
Route::middleware(['auth', 'password.check', 'role:analis,admin,supervisor,manajer'])
    ->prefix('dashboard/arsip-laporan')
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
