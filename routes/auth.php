<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('change-password', [AuthController::class, 'showChangePassword'])
        ->name('password.change');

    Route::post('change-password', [AuthController::class, 'updateChangePassword'])
        ->name('password.change.update');

    Route::post('logout', [AuthController::class, 'destroy'])
        ->name('logout');

    Route::get('logout', [AuthController::class, 'destroy'])
        ->name('logout.get');
});
