<?php

namespace App\Enums;

enum SetType: string
{
    case Normal = 'normal';
    case WarmUp = 'warm_up';
    case TopSet = 'top_set';
    case BackOff = 'back_off';
    case DropSet = 'drop_set';
    case Failure = 'failure';
    case AMRAP = 'amrap';
    case RestPause = 'rest_pause';
    case Cluster = 'cluster';

    public function label(): string
    {
        return match ($this) {
            self::Normal => 'Normal',
            self::WarmUp => 'Warm-up',
            self::TopSet => 'Top Set',
            self::BackOff => 'Back-off',
            self::DropSet => 'Drop Set',
            self::Failure => 'Failure',
            self::AMRAP => 'AMRAP',
            self::RestPause => 'Rest-pause',
            self::Cluster => 'Cluster',
        };
    }

    /**
     * Get default rest time in seconds for this set type.
     */
    public function defaultRestSeconds(): int
    {
        return match ($this) {
            self::TopSet => 180,
            self::Normal => 120,
            self::BackOff => 90,
            self::WarmUp, self::DropSet, self::Failure, self::AMRAP, self::RestPause, self::Cluster => 60,
        };
    }
}
