<?php

namespace App\Enums;

enum MuscleGroup: string
{
    case Chest = 'chest';
    case Back = 'back';
    case Shoulders = 'shoulders';
    case Biceps = 'biceps';
    case Triceps = 'triceps';
    case Forearms = 'forearms';
    case Abs = 'abs';
    case Obliques = 'obliques';
    case Glutes = 'glutes';
    case Quads = 'quads';
    case Hamstrings = 'hamstrings';
    case Calves = 'calves';
    case Traps = 'traps';
    case Lats = 'lats';
    case LowerBack = 'lower_back';
    case Neck = 'neck';

    public function label(): string
    {
        return match ($this) {
            self::Chest => 'Chest',
            self::Back => 'Back',
            self::Shoulders => 'Shoulders',
            self::Biceps => 'Biceps',
            self::Triceps => 'Triceps',
            self::Forearms => 'Forearms',
            self::Abs => 'Abs',
            self::Obliques => 'Obliques',
            self::Glutes => 'Glutes',
            self::Quads => 'Quads',
            self::Hamstrings => 'Hamstrings',
            self::Calves => 'Calves',
            self::Traps => 'Traps',
            self::Lats => 'Lats',
            self::LowerBack => 'Lower Back',
            self::Neck => 'Neck',
        };
    }
}
