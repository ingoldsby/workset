<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkoutPreference extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'user_id',
        'weekly_schedule',
        'focus_areas',
        'analysis_window_days',
        'preferences',
    ];

    protected function casts(): array
    {
        return [
            'weekly_schedule' => 'array',
            'focus_areas' => 'array',
            'analysis_window_days' => 'integer',
            'preferences' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getWeeklyScheduleForDay(string $day): ?array
    {
        return $this->weekly_schedule[$day] ?? null;
    }

    public function hasFocusArea(string $muscleGroup): bool
    {
        return in_array($muscleGroup, $this->focus_areas ?? []);
    }
}
