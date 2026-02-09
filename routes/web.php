<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\BranchesController;
use App\Http\Controllers\Users\ShiftController;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';

Route::middleware(['auth', 'role:admin,super_admin'])->group(function () {
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('users', [UserController::class, 'store'])->name('users.store');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    Route::get('branches', [BranchesController::class, 'index'])->name('branches.index');
});


// Route::middleware(['auth', 'verified', 'check.shift'])->group(function () {
//     // Your POS routes here
//     Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
//     // ... other POS routes
// });

Route::middleware(['auth', 'verified'])->group(function () {
    // Shift routes (outside check.shift middleware)
    Route::get('/shifts/open', [ShiftController::class, 'open'])->name('shifts.open');
    Route::post('/shifts', [ShiftController::class, 'store'])->name('shifts.store');
    Route::get('/shifts/close', [ShiftController::class, 'close'])->name('shifts.close');
    Route::put('/shifts', [ShiftController::class, 'update'])->name('shifts.update');
    Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.index');
});