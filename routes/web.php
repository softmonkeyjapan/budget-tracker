<?php

use App\Domains\Categories\Http\Controllers\CategoriesController;
use App\Domains\Expenses\Http\Controllers\BciWebhookController;
use App\Domains\Expenses\Http\Controllers\EcheanceOccurrencesController;
use App\Domains\Expenses\Http\Controllers\EcheancesController;
use App\Domains\Expenses\Http\Controllers\ExpensesController;
use App\Domains\Expenses\Http\Controllers\PendingExpensesController;
use App\Domains\Feedback\Http\Controllers\FeedbackController;
use App\Domains\Incomes\Http\Controllers\IncomesController;
use App\Domains\Reporting\Http\Controllers\ComparisonController;
use App\Domains\Reporting\Http\Controllers\DashboardController;
use App\Domains\Users\Http\Controllers\ProfileController;
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
    Route::get('expenses/pending', [PendingExpensesController::class, 'index'])->name('pending-expenses.index');
    Route::patch('expenses/{expense}/reject', [PendingExpensesController::class, 'reject'])->name('pending-expenses.reject');
    Route::resource('expenses', ExpensesController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::get('echeances/create', [EcheancesController::class, 'create'])->name('echeances.create');
    Route::get('echeances', [EcheancesController::class, 'index'])->name('echeances.index');
    Route::post('echeances', [EcheancesController::class, 'store'])->name('echeances.store');
    Route::patch('echeances/{echeance}/cancel', [EcheancesController::class, 'cancel'])->name('echeances.cancel');
    Route::patch('echeances/occurrences/{echeanceOccurrence}', [EcheanceOccurrencesController::class, 'update'])->name('echeance-occurrences.update');

    Route::get('incomes/create', [IncomesController::class, 'create'])->name('incomes.create');
    Route::resource('incomes', IncomesController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::post('/feedback', [FeedbackController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('feedback.store');
});

Route::post('/webhooks/bci', [BciWebhookController::class, 'store'])
    ->middleware(['throttle:60,1', 'webhook.bci'])
    ->name('webhooks.bci.store');

require __DIR__.'/auth.php';
