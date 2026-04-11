<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\ReportTypeManagementController;
use App\Http\Controllers\TugasPelaporanController;
use App\Http\Controllers\AnalisLaporanController;
use App\Http\Controllers\SupervisorLaporanController;
use App\Http\Controllers\ManajerLaporanController;
use App\Http\Controllers\ArsipLaporanController;
use App\Http\Controllers\LokasiController;
use App\Http\Controllers\PasswordSettingController;
use App\Http\Controllers\RuanganController;
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
        Route::resource('users', UserManagementController::class)->names('users');
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('settings', [PasswordSettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [PasswordSettingController::class, 'update'])->name('settings.update');

        // Jenis Laporan CRUD — dipindah ke admin

    });

// Tugas Pelaporan (hanya Admin QC)
Route::middleware(['auth', 'password.check', 'role:admin'])
    ->prefix('dashboard')
    ->group(function () {
        Route::resource('tugas-pelaporan', TugasPelaporanController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
            ->names('tugas-pelaporan');
        Route::get('tugas-pelaporan/{report}/preview', [AnalisLaporanController::class, 'lihat'])->name('admin.laporan.preview');
        // Data Master
        Route::resource('master/ruangan', RuanganController::class)->names('master.ruangan');
        Route::resource('master/lokasi', LokasiController::class)->names('master.lokasi');
        // Manajemen Laporan
        Route::resource('report-types', ReportTypeManagementController::class)->names('report-types');
        Route::post('report-types/{reportType}/sections', [ReportTypeManagementController::class, 'storeSection'])->name('report-types.sections.store');
        Route::put('report-types/{reportType}/sections/{section}', [ReportTypeManagementController::class, 'updateSection'])->name('report-types.sections.update');
        Route::delete('report-types/{reportType}/sections/{section}', [ReportTypeManagementController::class, 'destroySection'])->name('report-types.sections.destroy');
        Route::post('report-types/{reportType}/sections/{section}/locations', [ReportTypeManagementController::class, 'storeLocation'])->name('report-types.sections.locations.store');
        Route::delete('report-types/{reportType}/sections/{section}/locations/{location}', [ReportTypeManagementController::class, 'destroyLocation'])->name('report-types.sections.locations.destroy');
    });

// Dashboard & Laporan Analis
Route::middleware(['auth', 'password.check', 'role:analis'])
    ->prefix('dashboard')
    ->group(function () {
        Route::get('analis', function () {
            return view('pages.dashboard.analis');
        })->name('dashboard.analis');

        Route::get('laporan', [AnalisLaporanController::class, 'index'])->name('laporan.index');
        Route::get('laporan/{report}/isi', [AnalisLaporanController::class, 'isi'])->name('laporan.isi');
        Route::get('laporan/{report}/lihat', [AnalisLaporanController::class, 'lihat'])->name('laporan.lihat');
        Route::post('laporan/{report}/save', [AnalisLaporanController::class, 'save'])->name('laporan.save');
        Route::post('laporan/verify-password', [AnalisLaporanController::class, 'verifyPassword'])->name('laporan.verify-password');
    });

// Dashboard & Laporan Masuk Supervisor
Route::middleware(['auth', 'password.check', 'role:supervisor'])
    ->prefix('dashboard')
    ->group(function () {
        Route::get('supervisor', [SupervisorLaporanController::class, 'dashboard'])->name('dashboard.supervisor');
        Route::get('supervisor/laporan-masuk', [SupervisorLaporanController::class, 'laporanMasuk'])->name('supervisor.laporan-masuk');
        Route::get('supervisor/laporan/{report}/preview', [AnalisLaporanController::class, 'lihat'])->name('supervisor.laporan.preview');
        Route::get('supervisor/laporan/{report}', [SupervisorLaporanController::class, 'show'])->name('supervisor.laporan.show');
        Route::get('supervisor/laporan/{report}/cetak', [SupervisorLaporanController::class, 'cetak'])->name('supervisor.laporan.cetak');
        Route::post('supervisor/laporan/{report}/approve', [SupervisorLaporanController::class, 'approve'])->name('supervisor.laporan.approve');
        Route::post('supervisor/laporan/{report}/return', [SupervisorLaporanController::class, 'returnReport'])->name('supervisor.laporan.return');
    });

// Dashboard & Laporan Masuk Manajer
Route::middleware(['auth', 'password.check', 'role:manajer'])
    ->prefix('dashboard')
    ->group(function () {
        Route::get('manajer', [ManajerLaporanController::class, 'dashboard'])->name('dashboard.manajer');
        Route::get('manajer/laporan-masuk', [ManajerLaporanController::class, 'laporanMasuk'])->name('manajer.laporan-masuk');
        Route::get('manajer/laporan/{report}', [ManajerLaporanController::class, 'show'])->name('manajer.laporan.show');
        Route::get('manajer/laporan/{report}/cetak', [ManajerLaporanController::class, 'cetak'])->name('manajer.laporan.cetak');
        Route::post('manajer/laporan/{report}/approve', [ManajerLaporanController::class, 'approve'])->name('manajer.laporan.approve');
        Route::post('manajer/laporan/{report}/return', [ManajerLaporanController::class, 'returnReport'])->name('manajer.laporan.return');
    });

// Arsip Laporan (analis, admin, supervisor, manajer)
Route::middleware(['auth', 'password.check', 'role:analis,admin,supervisor,manajer'])
    ->prefix('dashboard/arsip-laporan')
    ->group(function () {
        Route::get('/', [ArsipLaporanController::class, 'index'])->name('arsip-laporan.index');
        Route::get('/{report}', [ArsipLaporanController::class, 'show'])->name('arsip-laporan.show');
    });

Route::middleware(['auth', 'password.check'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
