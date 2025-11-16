<?php

namespace App\Enums;

enum ExerciseCategory: string
{
    case Strength = 'strength';
    case Cardio = 'cardio';
    case Stretching = 'stretching';
    case Plyometrics = 'plyometrics';
    case Strongman = 'strongman';
    case Powerlifting = 'powerlifting';
    case Olympic = 'olympic';

    public function label(): string
    {
        return match ($this) {
            self::Strength => 'Strength',
            self::Cardio => 'Cardio',
            self::Stretching => 'Stretching',
            self::Plyometrics => 'Plyometrics',
            self::Strongman => 'Strongman',
            self::Powerlifting => 'Powerlifting',
            self::Olympic => 'Olympic',
        };
    }
}
