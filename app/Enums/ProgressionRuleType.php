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
    case WavePeriodisation = 'wave_periodisation';
    case PercentageBased = 'percentage_based';
    case DailyUndulation = 'daily_undulation';
    case BlockPeriodisation = 'block_periodisation';

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
            self::WavePeriodisation => 'Wave Periodisation',
            self::PercentageBased => 'Percentage-Based Programming',
            self::DailyUndulation => 'Daily Undulating Periodisation',
            self::BlockPeriodisation => 'Block Periodisation',
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
            self::WavePeriodisation => 'Progressive wave loading with weekly intensity variation',
            self::PercentageBased => 'Programme based on percentage of 1RM or training max',
            self::DailyUndulation => 'Vary intensity and volume daily within each week',
            self::BlockPeriodisation => 'Organise training into distinct mesocycle blocks',
        };
    }
}
