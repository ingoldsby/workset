<?php

namespace App\Enums;

enum EquipmentType: string
{
    case Barbell = 'barbell';
    case Dumbbell = 'dumbbell';
    case SmithMachine = 'smith_machine';
    case Cable = 'cable';
    case Machine = 'machine';
    case Kettlebell = 'kettlebell';
    case Bodyweight = 'bodyweight';
    case Band = 'band';
    case TrapBar = 'trap_bar';
    case SafetySquatBar = 'safety_squat_bar';
    case EZBar = 'ez_bar';
    case Sled = 'sled';
    case BosuBall = 'bosu_ball';

    public function label(): string
    {
        return match ($this) {
            self::Barbell => 'Barbell',
            self::Dumbbell => 'Dumbbell',
            self::SmithMachine => 'Smith Machine',
            self::Cable => 'Cable',
            self::Machine => 'Machine',
            self::Kettlebell => 'Kettlebell',
            self::Bodyweight => 'Bodyweight',
            self::Band => 'Band',
            self::TrapBar => 'Trap Bar',
            self::SafetySquatBar => 'Safety Squat Bar',
            self::EZBar => 'EZ Bar',
            self::Sled => 'Sled',
            self::BosuBall => 'Bosu Ball',
        };
    }
}
