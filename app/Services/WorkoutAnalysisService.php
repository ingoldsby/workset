<?php

namespace App\Services;

use App\Models\User;
use App\Models\TrainingSession;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WorkoutAnalysisService
{
    public function analyzeUserWorkouts(User $user, int $daysBack = 14): array
    {
        $sessions = $this->getRecentSessions($user, $daysBack);

        if ($sessions->isEmpty()) {
            return $this->getEmptyAnalysis();
        }

        return [
            'period' => [
                'days' => $daysBack,
                'start_date' => now()->subDays($daysBack)->toDateString(),
                'end_date' => now()->toDateString(),
            ],
            'session_summary' => $this->analyzeSessionSummary($sessions),
            'muscle_groups' => $this->analyzeMuscleGroups($sessions),
            'exercise_categories' => $this->analyzeExerciseCategories($sessions),
            'volume_metrics' => $this->analyzeVolumeMetrics($sessions),
            'cardio_analysis' => $this->analyzeCardio($user, $daysBack),
            'weekly_patterns' => $this->analyzeWeeklyPatterns($sessions),
            'recovery_analysis' => $this->analyzeRecovery($sessions),
        ];
    }

    protected function getRecentSessions(User $user, int $daysBack): Collection
    {
        return TrainingSession::where('user_id', $user->id)
            ->where('completed_at', '>=', now()->subDays($daysBack))
            ->whereNotNull('completed_at')
            ->with([
                'exercises.exercise',
                'exercises.sets',
                'cardioEntries',
            ])
            ->orderBy('completed_at', 'desc')
            ->get();
    }

    protected function analyzeSessionSummary(Collection $sessions): array
    {
        $totalSessions = $sessions->count();
        $averageSessionsPerWeek = ($totalSessions / 14) * 7;

        return [
            'total_sessions' => $totalSessions,
            'average_per_week' => round($averageSessionsPerWeek, 1),
            'completion_rate' => 100,
        ];
    }

    protected function analyzeMuscleGroups(Collection $sessions): array
    {
        $muscleGroupFrequency = [];
        $muscleGroupVolume = [];

        foreach ($sessions as $session) {
            foreach ($session->exercises as $sessionExercise) {
                $exercise = $sessionExercise->exercise;

                if (! $exercise || ! $exercise->primary_muscle) {
                    continue;
                }

                $muscleGroup = $exercise->primary_muscle->value;

                if (! isset($muscleGroupFrequency[$muscleGroup])) {
                    $muscleGroupFrequency[$muscleGroup] = 0;
                    $muscleGroupVolume[$muscleGroup] = 0;
                }

                $muscleGroupFrequency[$muscleGroup]++;
                $muscleGroupVolume[$muscleGroup] += $sessionExercise->sets->count();
            }
        }

        arsort($muscleGroupFrequency);

        return [
            'frequency' => $muscleGroupFrequency,
            'volume' => $muscleGroupVolume,
            'most_trained' => array_key_first($muscleGroupFrequency) ?? null,
            'least_trained' => array_key_last($muscleGroupFrequency) ?? null,
        ];
    }

    protected function analyzeExerciseCategories(Collection $sessions): array
    {
        $categoryFrequency = [];

        foreach ($sessions as $session) {
            foreach ($session->exercises as $sessionExercise) {
                $exercise = $sessionExercise->exercise;

                if (! $exercise || ! $exercise->category) {
                    continue;
                }

                $category = $exercise->category->value;
                $categoryFrequency[$category] = ($categoryFrequency[$category] ?? 0) + 1;
            }
        }

        arsort($categoryFrequency);

        return [
            'frequency' => $categoryFrequency,
            'primary_category' => array_key_first($categoryFrequency) ?? 'strength',
        ];
    }

    protected function analyzeVolumeMetrics(Collection $sessions): array
    {
        $totalSets = 0;
        $totalReps = 0;
        $totalWeight = 0;
        $setCount = 0;

        foreach ($sessions as $session) {
            foreach ($session->exercises as $sessionExercise) {
                foreach ($sessionExercise->sets as $set) {
                    if ($set->isCompleted()) {
                        $totalSets++;
                        $totalReps += $set->performed_reps ?? 0;
                        $totalWeight += ($set->performed_weight ?? 0) * ($set->performed_reps ?? 0);
                        $setCount++;
                    }
                }
            }
        }

        return [
            'total_sets' => $totalSets,
            'average_sets_per_session' => $sessions->count() > 0 ? round($totalSets / $sessions->count(), 1) : 0,
            'average_reps_per_set' => $setCount > 0 ? round($totalReps / $setCount, 1) : 0,
            'total_volume' => round($totalWeight, 2),
        ];
    }

    protected function analyzeCardio(User $user, int $daysBack): array
    {
        $cardioEntries = $user->cardioEntries()
            ->where('entry_date', '>=', now()->subDays($daysBack))
            ->get();

        $cardioTypes = [];
        $totalDuration = 0;
        $totalDistance = 0;

        foreach ($cardioEntries as $entry) {
            $type = $entry->cardio_type->value;
            $cardioTypes[$type] = ($cardioTypes[$type] ?? 0) + 1;
            $totalDuration += $entry->duration_seconds ?? 0;
            $totalDistance += $entry->distance ?? 0;
        }

        arsort($cardioTypes);

        return [
            'total_sessions' => $cardioEntries->count(),
            'types' => $cardioTypes,
            'most_frequent_type' => array_key_first($cardioTypes) ?? null,
            'total_duration_minutes' => round($totalDuration / 60, 1),
            'total_distance' => round($totalDistance, 2),
            'average_per_week' => ($cardioEntries->count() / ($daysBack / 7)),
        ];
    }

    protected function analyzeWeeklyPatterns(Collection $sessions): array
    {
        $dayOfWeekFrequency = [];
        $dayMuscleGroups = [];

        foreach ($sessions as $session) {
            $dayOfWeek = $session->completed_at->format('l');

            $dayOfWeekFrequency[$dayOfWeek] = ($dayOfWeekFrequency[$dayOfWeek] ?? 0) + 1;

            if (! isset($dayMuscleGroups[$dayOfWeek])) {
                $dayMuscleGroups[$dayOfWeek] = [];
            }

            foreach ($session->exercises as $sessionExercise) {
                $exercise = $sessionExercise->exercise;

                if ($exercise && $exercise->primary_muscle) {
                    $muscle = $exercise->primary_muscle->value;
                    $dayMuscleGroups[$dayOfWeek][$muscle] = ($dayMuscleGroups[$dayOfWeek][$muscle] ?? 0) + 1;
                }
            }
        }

        foreach ($dayMuscleGroups as $day => $muscles) {
            arsort($dayMuscleGroups[$day]);
        }

        return [
            'training_days' => array_keys($dayOfWeekFrequency),
            'day_frequency' => $dayOfWeekFrequency,
            'day_muscle_patterns' => $dayMuscleGroups,
        ];
    }

    protected function analyzeRecovery(Collection $sessions): array
    {
        $sessions = $sessions->sortBy('completed_at');
        $gaps = [];

        $previousSession = null;

        foreach ($sessions as $session) {
            if ($previousSession) {
                $daysBetween = $previousSession->completed_at->diffInDays($session->completed_at);
                $gaps[] = $daysBetween;
            }

            $previousSession = $session;
        }

        return [
            'average_days_between_sessions' => count($gaps) > 0 ? round(array_sum($gaps) / count($gaps), 1) : 0,
            'min_rest_days' => count($gaps) > 0 ? min($gaps) : 0,
            'max_rest_days' => count($gaps) > 0 ? max($gaps) : 0,
        ];
    }

    protected function getEmptyAnalysis(): array
    {
        return [
            'period' => [
                'days' => 0,
                'start_date' => null,
                'end_date' => null,
            ],
            'session_summary' => [
                'total_sessions' => 0,
                'average_per_week' => 0,
                'completion_rate' => 0,
            ],
            'muscle_groups' => [
                'frequency' => [],
                'volume' => [],
                'most_trained' => null,
                'least_trained' => null,
            ],
            'exercise_categories' => [
                'frequency' => [],
                'primary_category' => 'strength',
            ],
            'volume_metrics' => [
                'total_sets' => 0,
                'average_sets_per_session' => 0,
                'average_reps_per_set' => 0,
                'total_volume' => 0,
            ],
            'cardio_analysis' => [
                'total_sessions' => 0,
                'types' => [],
                'most_frequent_type' => null,
                'total_duration_minutes' => 0,
                'total_distance' => 0,
                'average_per_week' => 0,
            ],
            'weekly_patterns' => [
                'training_days' => [],
                'day_frequency' => [],
                'day_muscle_patterns' => [],
            ],
            'recovery_analysis' => [
                'average_days_between_sessions' => 0,
                'min_rest_days' => 0,
                'max_rest_days' => 0,
            ],
        ];
    }
}
