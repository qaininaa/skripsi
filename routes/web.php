<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\TugasPelaporanController;
use App\Http\Controllers\AnalisLaporanController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Redirect /dashboard berdasarkan role
Route::get('/dashboard', function () {
    $role = Auth::user()->role;
    if ($role === 'super admin') {
        return redirect()->route('dashboard.super-admin');
    } elseif ($role === 'admin-qc') {
        return redirect()->route('dashboard.admin-qc');
    } elseif ($role === 'analis') {
        return redirect()->route('dashboard.analis');
    }
    return redirect('/');
})->middleware('auth')->name('dashboard');

// Dashboard Super Admin
Route::get('/dashboard/super-admin', function () {
    return view('dashboard.super-admin');
})->middleware(['auth', 'role:super admin'])->name('dashboard.super-admin');

// Dashboard Admin QC
Route::get('/dashboard/admin-qc', function () {
    return view('dashboard.admin-qc');
})->middleware(['auth', 'role:admin-qc'])->name('dashboard.admin-qc');

// Manajemen Pengguna (hanya Super Admin)
Route::middleware(['auth', 'role:super admin'])
    ->prefix('dashboard')
    ->group(function () {
        Route::resource('users', UserManagementController::class)->names('users');
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });

// Tugas Pelaporan (hanya Admin QC)
Route::middleware(['auth', 'role:admin-qc'])
    ->prefix('dashboard')
    ->group(function () {
        Route::resource('tugas-pelaporan', TugasPelaporanController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
            ->names('tugas-pelaporan');
    });

// Dashboard & Laporan Analis
Route::middleware(['auth', 'role:analis'])
    ->prefix('dashboard')
    ->group(function () {
        Route::get('analis', function () {
            return view('dashboard.analis');
        })->name('dashboard.analis');

        Route::get('laporan', [AnalisLaporanController::class, 'index'])->name('laporan.index');
        Route::get('laporan/{report}/isi', [AnalisLaporanController::class, 'isi'])->name('laporan.isi');
        Route::post('laporan/{report}/save', [AnalisLaporanController::class, 'save'])->name('laporan.save');
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
