<?php

namespace App\Enums;

enum CardioType: string
{
    case Running = 'running';
    case Walking = 'walking';
    case Cycling = 'cycling';
    case Rowing = 'rowing';
    case Elliptical = 'elliptical';
    case Treadmill = 'treadmill';
    case IndoorBike = 'indoor_bike';
    case Boxing = 'boxing';

    public function label(): string
    {
        return match ($this) {
            self::Running => 'Running',
            self::Walking => 'Walking',
            self::Cycling => 'Cycling',
            self::Rowing => 'Rowing',
            self::Elliptical => 'Elliptical',
            self::Treadmill => 'Treadmill',
            self::IndoorBike => 'Indoor Bike',
            self::Boxing => 'Boxing',
        };
    }

    public function showDistanceByDefault(): bool
    {
        return match ($this) {
            self::Running, self::Walking, self::Cycling => true,
            self::Rowing, self::Elliptical, self::Treadmill, self::IndoorBike, self::Boxing => false,
        };
    }
}
