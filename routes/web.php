<?php

use App\Domains\Users\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\ComparisonController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\IncomesController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
});

Route::get('/dashboard', [DashboardController::class, 'show'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/comparison', [ComparisonController::class, 'show'])
    ->middleware(['auth', 'verified'])
    ->name('comparison');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('categories', CategoriesController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::get('expenses/create', [ExpensesController::class, 'create'])->name('expenses.create');
    Route::resource('expenses', ExpensesController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::get('incomes/create', [IncomesController::class, 'create'])->name('incomes.create');
    Route::resource('incomes', IncomesController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::post('/feedback', [FeedbackController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('feedback.store');
});

require __DIR__.'/auth.php';
