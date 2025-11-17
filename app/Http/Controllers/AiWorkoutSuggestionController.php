<?php

namespace App\Http\Controllers;

use App\Actions\GenerateWorkoutSuggestionAction;
use App\Enums\SuggestionType;
use App\Http\Requests\GenerateWorkoutSuggestionRequest;
use App\Models\AiWorkoutSuggestion;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiWorkoutSuggestionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = $request->input('user_id');

        if (! $userId) {
            return response()->json([
                'message' => 'user_id parameter is required.',
            ], 400);
        }

        $user = User::findOrFail($userId);

        $this->authorize('viewAny', [AiWorkoutSuggestion::class, $user]);

        $suggestions = AiWorkoutSuggestion::where('user_id', $userId)
            ->with(['generatedBy:id,name'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($suggestions);
    }

    public function show(AiWorkoutSuggestion $aiWorkoutSuggestion): JsonResponse
    {
        $this->authorize('view', $aiWorkoutSuggestion);

        $aiWorkoutSuggestion->load(['user:id,name', 'generatedBy:id,name']);

        return response()->json([
            'data' => $aiWorkoutSuggestion,
        ]);
    }

    public function generate(
        GenerateWorkoutSuggestionRequest $request,
        GenerateWorkoutSuggestionAction $action
    ): JsonResponse {
        $member = User::findOrFail($request->validated('user_id'));
        $type = SuggestionType::from($request->validated('suggestion_type'));

        try {
            $suggestion = $action->execute(
                member: $member,
                pt: $request->user(),
                type: $type,
                customPrompt: $request->validated('custom_prompt'),
                analysisWindowDays: $request->validated('analysis_window_days')
            );

            $suggestion->load(['user:id,name', 'generatedBy:id,name']);

            return response()->json([
                'message' => 'Workout suggestion generated successfully.',
                'data' => $suggestion,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to generate workout suggestion.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function markAsApplied(
        Request $request,
        AiWorkoutSuggestion $aiWorkoutSuggestion
    ): JsonResponse {
        $this->authorize('update', $aiWorkoutSuggestion);

        $validated = $request->validate([
            'session_id' => ['nullable', 'exists:training_sessions,id'],
            'program_id' => ['nullable', 'exists:programs,id'],
        ]);

        $aiWorkoutSuggestion->markAsApplied(
            $validated['session_id'] ?? null,
            $validated['program_id'] ?? null
        );

        return response()->json([
            'message' => 'Suggestion marked as applied successfully.',
            'data' => $aiWorkoutSuggestion->fresh(),
        ]);
    }

    public function destroy(AiWorkoutSuggestion $aiWorkoutSuggestion): JsonResponse
    {
        $this->authorize('delete', $aiWorkoutSuggestion);

        $aiWorkoutSuggestion->delete();

        return response()->json([
            'message' => 'Suggestion deleted successfully.',
        ]);
    }
}
