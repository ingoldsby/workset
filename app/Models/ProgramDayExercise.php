<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramDayExercise extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'program_day_id',
        'exercise_id',
        'member_exercise_id',
        'order',
        'superset_group',
        'sets',
        'reps_min',
        'reps_max',
        'rpe',
        'rest_seconds',
        'tempo',
        'notes',
        'progression_rules',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'sets' => 'integer',
            'reps_min' => 'integer',
            'reps_max' => 'integer',
            'rpe' => 'integer',
            'rest_seconds' => 'integer',
            'progression_rules' => 'array',
        ];
    }

    public function programDay(): BelongsTo
    {
        return $this->belongsTo(ProgramDay::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function memberExercise(): BelongsTo
    {
        return $this->belongsTo(MemberExercise::class);
    }

    public function getExerciseName(): string
    {
        return $this->exercise?->name ?? $this->memberExercise?->name ?? 'Unknown Exercise';
    }
}
