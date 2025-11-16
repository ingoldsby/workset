<?php

use Illuminate\Support\Facades\Route;

/**
 * PWA / Push Notification API Routes
 */
Route::middleware('auth:sanctum')->group(function () {
    // Get VAPID public key for push notifications
    Route::get('/push/vapid-public-key', function () {
        return response()->json([
            'publicKey' => config('services.vapid.public_key'),
        ]);
    });

    // Subscribe to push notifications
    Route::post('/push/subscribe', function (Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'endpoint' => ['required', 'url'],
            'keys' => ['required', 'array'],
            'keys.p256dh' => ['required', 'string'],
            'keys.auth' => ['required', 'string'],
        ]);

        // Store subscription in database
        $request->user()->pushSubscriptions()->updateOrCreate(
            ['endpoint' => $validated['endpoint']],
            [
                'p256dh' => $validated['keys']['p256dh'],
                'auth' => $validated['keys']['auth'],
            ]
        );

        return response()->json(['success' => true]);
    });

    // Unsubscribe from push notifications
    Route::post('/push/unsubscribe', function (Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'endpoint' => ['required', 'url'],
        ]);

        $request->user()->pushSubscriptions()
            ->where('endpoint', $validated['endpoint'])
            ->delete();

        return response()->json(['success' => true]);
    });
});

/**
 * Offline sync API routes
 */
Route::middleware('auth:sanctum')->group(function () {
    // Sync offline session sets
    Route::post('/session-sets', function (Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'training_session_id' => ['required', 'string', 'exists:training_sessions,id'],
            'exercise_id' => ['nullable', 'string', 'exists:exercises,id'],
            'member_exercise_id' => ['nullable', 'string', 'exists:member_exercises,id'],
            'set_number' => ['required', 'integer', 'min:1'],
            'weight_performed' => ['nullable', 'numeric', 'min:0'],
            'reps_performed' => ['nullable', 'integer', 'min:0'],
            'rpe_performed' => ['nullable', 'numeric', 'min:1', 'max:10'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $set = App\Models\SessionSet::create($validated);

        return response()->json($set, 201);
    });

    // Complete a session
    Route::post('/sessions/{session}/complete', function (Illuminate\Http\Request $request, string $sessionId) {
        $session = App\Models\TrainingSession::findOrFail($sessionId);

        // Authorise user
        if ($session->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'completed_at' => ['required', 'date'],
        ]);

        $session->update([
            'completed_at' => $validated['completed_at'],
        ]);

        return response()->json($session);
    });
});

/**
 * Workout Preferences API Routes
 */
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users/{user}/workout-preference', [App\Http\Controllers\WorkoutPreferenceController::class, 'show'])
        ->name('workoutPreference.show');

    Route::post('/workout-preferences', [App\Http\Controllers\WorkoutPreferenceController::class, 'store'])
        ->name('workoutPreference.store');

    Route::put('/workout-preferences/{workoutPreference}', [App\Http\Controllers\WorkoutPreferenceController::class, 'update'])
        ->name('workoutPreference.update');

    Route::delete('/workout-preferences/{workoutPreference}', [App\Http\Controllers\WorkoutPreferenceController::class, 'destroy'])
        ->name('workoutPreference.destroy');
});

/**
 * AI Workout Suggestions API Routes
 */
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/ai-workout-suggestions', [App\Http\Controllers\AiWorkoutSuggestionController::class, 'index'])
        ->name('aiWorkoutSuggestion.index');

    Route::get('/ai-workout-suggestions/{aiWorkoutSuggestion}', [App\Http\Controllers\AiWorkoutSuggestionController::class, 'show'])
        ->name('aiWorkoutSuggestion.show');

    Route::post('/ai-workout-suggestions/generate', [App\Http\Controllers\AiWorkoutSuggestionController::class, 'generate'])
        ->name('aiWorkoutSuggestion.generate');

    Route::post('/ai-workout-suggestions/{aiWorkoutSuggestion}/apply', [App\Http\Controllers\AiWorkoutSuggestionController::class, 'markAsApplied'])
        ->name('aiWorkoutSuggestion.apply');

    Route::delete('/ai-workout-suggestions/{aiWorkoutSuggestion}', [App\Http\Controllers\AiWorkoutSuggestionController::class, 'destroy'])
        ->name('aiWorkoutSuggestion.destroy');
});
