<?php

namespace App\Enums;

enum ProgressionRuleType: string
{
    case LinearProgression = 'linear_progression';
    case DoubleProgression = 'double_progression';
    case TopSetBackoff = 'top_set_backoff';
    case RpeTarget = 'rpe_target';
    case PlannedDeload = 'planned_deload';
    case WeeklyUndulation = 'weekly_undulation';
    case CustomWarmup = 'custom_warmup';

    public function label(): string
    {
        return match ($this) {
            self::LinearProgression => 'Linear Progression',
            self::DoubleProgression => 'Double Progression',
            self::TopSetBackoff => 'Top Set + Back-off',
            self::RpeTarget => 'RPE Target',
            self::PlannedDeload => 'Planned Deload',
            self::WeeklyUndulation => 'Weekly Undulation',
            self::CustomWarmup => 'Custom Warm-up',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::LinearProgression => 'Add weight each session or week until cap is reached',
            self::DoubleProgression => 'Increase reps within range, then add weight and drop reps',
            self::TopSetBackoff => 'Heavy top set followed by lighter back-off sets',
            self::RpeTarget => 'Target RPE with tolerance for auto-regulation',
            self::PlannedDeload => 'Scheduled deloads every N weeks by percentage',
            self::WeeklyUndulation => 'Rotate between heavy, medium, and light days',
            self::CustomWarmup => 'Define specific warm-up sets before working sets',
        };
    }
}
