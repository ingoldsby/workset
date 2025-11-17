<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkoutPreferenceRequest;
use App\Http\Requests\UpdateWorkoutPreferenceRequest;
use App\Models\User;
use App\Models\WorkoutPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkoutPreferenceController extends Controller
{
    public function show(User $user): JsonResponse
    {
        $this->authorize('view', [WorkoutPreference::class, $user]);

        $preference = $user->workoutPreference;

        if (! $preference) {
            return response()->json([
                'message' => 'No workout preferences found for this user.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'data' => $preference,
        ]);
    }

    public function store(StoreWorkoutPreferenceRequest $request): JsonResponse
    {
        $user = User::findOrFail($request->validated('user_id'));

        $this->authorize('create', [WorkoutPreference::class, $user]);

        $existingPreference = $user->workoutPreference;

        if ($existingPreference) {
            return response()->json([
                'message' => 'Workout preferences already exist for this user. Use update instead.',
                'data' => $existingPreference,
            ], 409);
        }

        $preference = WorkoutPreference::create([
            'user_id' => $user->id,
            'weekly_schedule' => $request->validated('weekly_schedule'),
            'focus_areas' => $request->validated('focus_areas'),
            'analysis_window_days' => $request->validated('analysis_window_days', 14),
            'preferences' => $request->validated('preferences'),
        ]);

        return response()->json([
            'message' => 'Workout preferences created successfully.',
            'data' => $preference,
        ], 201);
    }

    public function update(UpdateWorkoutPreferenceRequest $request, WorkoutPreference $workoutPreference): JsonResponse
    {
        $workoutPreference->update($request->validated());

        return response()->json([
            'message' => 'Workout preferences updated successfully.',
            'data' => $workoutPreference->fresh(),
        ]);
    }

    public function destroy(WorkoutPreference $workoutPreference): JsonResponse
    {
        $this->authorize('delete', $workoutPreference);

        $workoutPreference->delete();

        return response()->json([
            'message' => 'Workout preferences deleted successfully.',
        ]);
    }
}
