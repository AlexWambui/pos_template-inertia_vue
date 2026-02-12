<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\BranchesController;
use App\Http\Controllers\Users\ShiftController;
use App\Http\Controllers\Products\ProductCategoryController;
use App\Http\Controllers\Products\ProductController;

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

    Route::get('product-categories', [ProductCategoryController::class, 'index'])->name('product-categories.index');
    Route::get('product-categories/create', [ProductCategoryController::class, 'create'])->name('product-categories.create');
    Route::post('product-categories', [ProductCategoryController::class, 'store'])->name('product-categories.store');
    Route::get('product-categories/{category}/edit', [ProductCategoryController::class, 'edit'])->name('product-categories.edit');
    Route::put('product-categories/{category}', [ProductCategoryController::class, 'update'])->name('product-categories.update');
    Route::delete('product-categories/{category}', [ProductCategoryController::class, 'destroy'])->name('product-categories.destroy');

    Route::get('products', [ProductController::class, 'index'])->name('products.index');
    Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('products', [ProductController::class, 'store'])->name('products.store');
    Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
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