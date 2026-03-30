<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Products\ProductController;

Route::get('/', function() {
    return redirect()->route('login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', 'role:super_admin'])->group(function () {
    Route::resource('branches', BranchController::class)->except('show');
    Route::resource('users', UserController::class)->except('show');
});

Route::middleware(['auth', 'role:super_admin,admin'])->group(function () {
    Route::resource('products', ProductController::class)->except('show');
});

require __DIR__.'/settings.php';
