<?php

namespace App\Enums;

enum ExerciseMechanics: string
{
    case Compound = 'compound';
    case Isolation = 'isolation';

    public function label(): string
    {
        return match ($this) {
            self::Compound => 'Compound',
            self::Isolation => 'Isolation',
        };
    }
}
