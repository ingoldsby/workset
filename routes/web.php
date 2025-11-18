<?php

use App\Http\Controllers\InviteController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('today.index');
})->middleware(['auth', 'verified']);

// Health check endpoint (accessible without auth for monitoring)
Route::get('/health', function () {
    return response()->json(['status' => 'ok'], 200);
})->name('health');

// Offline page (accessible without auth for PWA)
Route::get('/offline', fn () => view('offline'))->name('offline');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard alias for compatibility with Breeze tests
    Route::get('/dashboard', fn () => redirect()->route('today.index'))->name('dashboard');

    // Main app sections
    Route::get('/today', fn () => view('today.index'))->name('today.index');
    Route::get('/plan', fn () => view('plan.index'))->name('plan.index');
    Route::get('/log/{session?}', fn (?string $session = null) => view('log.index', ['session' => $session]))->name('log.index');
    Route::get('/programs', fn () => view('programs.index'))->name('programs.index');
    Route::get('/programs/progression-builder', fn () => view('programs.progression-builder'))->name('programs.progressionBuilder');
    Route::get('/exercises', fn () => view('exercises.index'))->name('exercises.index');
    Route::get('/history', fn () => view('history.index'))->name('history.index');
    Route::get('/analytics', fn () => view('analytics.index'))->name('analytics.index');

    // PT-only area
    Route::middleware('can:viewPtArea,App\Models\User')->group(function () {
        Route::get('/pt', fn () => view('pt.index'))->name('pt.index');
    });

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Invite management routes
    Route::resource('invites', InviteController::class)
        ->except(['show', 'edit', 'update']);

    Route::post('invites/{invite}/resend', [InviteController::class, 'resend'])
        ->name('invites.resend');
});

require __DIR__.'/auth.php';
