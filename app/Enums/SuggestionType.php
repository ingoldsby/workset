<?php

namespace App\Enums;

enum SuggestionType: string
{
    case SingleSession = 'single_session';
    case ExerciseList = 'exercise_list';
    case WeeklyProgram = 'weekly_program';

    public function label(): string
    {
        return match ($this) {
            self::SingleSession => 'Single Session',
            self::ExerciseList => 'Exercise List',
            self::WeeklyProgram => 'Weekly Program',
        };
    }
}
