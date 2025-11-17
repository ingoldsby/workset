<?php

namespace App\Actions;

use App\Enums\SuggestionType;
use App\Models\AiWorkoutSuggestion;
use App\Models\User;
use App\Services\AiWorkoutSuggestionService;
use App\Services\WorkoutAnalysisService;
use Illuminate\Support\Facades\DB;

class GenerateWorkoutSuggestionAction
{
    public function __construct(
        private WorkoutAnalysisService $workoutAnalysisService,
        private AiWorkoutSuggestionService $aiService,
    ) {}

    public function execute(
        User $member,
        User $pt,
        SuggestionType $type,
        ?string $customPrompt = null,
        ?int $analysisWindowDays = null
    ): AiWorkoutSuggestion {
        return DB::transaction(function () use ($member, $pt, $type, $customPrompt, $analysisWindowDays) {
            $preferences = $member->workoutPreference;
            $daysBack = $analysisWindowDays ?? $preferences?->analysis_window_days ?? 14;

            $analysisData = $this->workoutAnalysisService->analyzeUserWorkouts($member, $daysBack);

            $suggestionData = $this->aiService->generateSuggestion(
                $type,
                $member,
                $analysisData,
                $preferences,
                $customPrompt
            );

            $promptContext = [
                'type' => $type->value,
                'analysis_window_days' => $daysBack,
                'custom_prompt' => $customPrompt,
                'has_preferences' => $preferences !== null,
            ];

            return AiWorkoutSuggestion::create([
                'user_id' => $member->id,
                'generated_by' => $pt->id,
                'suggestion_type' => $type,
                'prompt_context' => $promptContext,
                'suggestion_data' => $suggestionData,
                'analysis_data' => $analysisData,
            ]);
        });
    }
}
