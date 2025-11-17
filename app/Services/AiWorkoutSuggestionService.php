<?php

namespace App\Services;

use App\Enums\SuggestionType;
use App\Models\User;
use App\Models\WorkoutPreference;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiWorkoutSuggestionService
{
    protected string $apiKey;
    protected string $model;
    protected int $maxTokens;
    protected float $temperature;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
        $this->model = config('services.openai.model');
        $this->maxTokens = config('services.openai.max_tokens');
        $this->temperature = config('services.openai.temperature');
    }

    public function generateSuggestion(
        SuggestionType $type,
        User $user,
        array $analysisData,
        ?WorkoutPreference $preferences = null,
        ?string $customPrompt = null
    ): array {
        $systemPrompt = $this->buildSystemPrompt();
        $userPrompt = $this->buildUserPrompt($type, $user, $analysisData, $preferences, $customPrompt);

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'max_tokens' => $this->maxTokens,
                'temperature' => $this->temperature,
                'response_format' => ['type' => 'json_object'],
            ]);

            if (! $response->successful()) {
                Log::error('OpenAI API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                throw new \Exception('Failed to generate AI suggestion: ' . $response->body());
            }

            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? null;

            if (! $content) {
                throw new \Exception('No content received from OpenAI');
            }

            return json_decode($content, true);
        } catch (\Exception $e) {
            Log::error('Error generating AI workout suggestion', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'type' => $type->value,
            ]);

            throw $e;
        }
    }

    protected function buildSystemPrompt(): string
    {
        return <<<'PROMPT'
You are an expert personal trainer and exercise physiologist specialising in workout programming. Your role is to analyse a user's training history and preferences to generate intelligent, evidence-based workout suggestions.

When generating workout suggestions, you must:
1. Consider the user's recent training history and identify areas that need attention
2. Ensure adequate recovery between sessions for the same muscle groups
3. Balance workout volume and intensity based on the user's experience level
4. Incorporate the user's preferences and schedule constraints
5. Provide specific exercise selections, sets, reps, and RPE (Rate of Perceived Exertion) prescriptions
6. Include exercise order and superset groupings where appropriate
7. Suggest appropriate rest periods between sets
8. Provide a clear rationale for your suggestions

Always respond with valid JSON in the following structure:
{
  "suggestion": {
    "title": "Descriptive title for the workout/program",
    "rationale": "Explanation of why these exercises were chosen",
    "exercises": [
      {
        "name": "Exercise name",
        "muscleGroup": "Primary muscle group",
        "category": "Exercise category (strength/cardio/etc)",
        "sets": number,
        "reps": "Rep range (e.g., '8-12' or '12')",
        "rpe": number (6-10),
        "restSeconds": number,
        "supersetGroup": number or null,
        "order": number,
        "notes": "Any specific form cues or variations"
      }
    ],
    "cardio": [
      {
        "type": "Cardio type (running/boxing/etc)",
        "durationMinutes": number,
        "notes": "Any specific instructions"
      }
    ],
    "programNotes": "Overall program notes and progression suggestions"
  }
}

For exercise lists, simplify the structure to focus on exercise recommendations.
For weekly programs, provide a "weeklySchedule" array with day-by-day breakdowns.
PROMPT;
    }

    protected function buildUserPrompt(
        SuggestionType $type,
        User $user,
        array $analysisData,
        ?WorkoutPreference $preferences,
        ?string $customPrompt
    ): string {
        $prompt = "Generate a {$type->label()} for the user based on the following information:\n\n";

        $prompt .= "TRAINING HISTORY (Last {$analysisData['period']['days']} days):\n";
        $prompt .= json_encode($analysisData, JSON_PRETTY_PRINT) . "\n\n";

        if ($preferences) {
            $prompt .= "USER PREFERENCES:\n";
            $prompt .= "Weekly Schedule: " . json_encode($preferences->weekly_schedule, JSON_PRETTY_PRINT) . "\n";
            $prompt .= "Focus Areas: " . json_encode($preferences->focus_areas) . "\n";
            $prompt .= "Additional Preferences: " . json_encode($preferences->preferences) . "\n\n";
        }

        if ($customPrompt) {
            $prompt .= "SPECIFIC REQUEST:\n{$customPrompt}\n\n";
        }

        $prompt .= $this->getTypeSpecificInstructions($type);

        return $prompt;
    }

    protected function getTypeSpecificInstructions(SuggestionType $type): string
    {
        return match ($type) {
            SuggestionType::SingleSession => <<<'INSTRUCTIONS'
Generate a single complete workout session with:
- 5-8 exercises (mix of compound and isolation movements)
- Specific sets, reps, and RPE for each exercise
- Exercise order and any supersets
- Rest periods
- Any cardio component if relevant to user's history
- Clear rationale for exercise selection based on recent training gaps
INSTRUCTIONS,

            SuggestionType::ExerciseList => <<<'INSTRUCTIONS'
Generate a recommended list of 8-12 exercises that would benefit the user based on:
- Muscle groups that haven't been trained recently
- Balance with their current training patterns
- Their stated focus areas
Provide brief notes on why each exercise was selected, but don't prescribe specific sets/reps.
INSTRUCTIONS,

            SuggestionType::WeeklyProgram => <<<'INSTRUCTIONS'
Generate a complete weekly training program with:
- 3-6 training sessions distributed across the week
- Each session should have 4-7 exercises
- Consider the user's weekly schedule preferences
- Ensure adequate recovery between muscle groups
- Include cardio sessions if the user regularly does cardio
- Provide progression guidelines
- Include a weekly overview and rationale
INSTRUCTIONS,
        };
    }
}
