<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ChangePasswordController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('change-password', [ChangePasswordController::class, 'show'])
        ->name('password.change');

    Route::post('change-password', [ChangePasswordController::class, 'update'])
        ->name('password.change.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    Route::get('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout.get');
});
