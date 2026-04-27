<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/dashboard'));
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/api/district-cni', [DashboardController::class, 'districtData'])->name('api.district-cni');
