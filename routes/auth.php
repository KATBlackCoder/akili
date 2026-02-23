<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\FirstLoginController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('first-login', [FirstLoginController::class, 'show'])->name('first-login');
    Route::post('first-login', [FirstLoginController::class, 'update']);
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
