<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalRecord extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'user_id',
        'exercise_id',
        'member_exercise_id',
        'record_type',
        'weight',
        'reps',
        'volume',
        'session_set_id',
        'achieved_at',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'reps' => 'integer',
            'volume' => 'decimal:2',
            'achieved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function memberExercise(): BelongsTo
    {
        return $this->belongsTo(MemberExercise::class);
    }

    public function sessionSet(): BelongsTo
    {
        return $this->belongsTo(SessionSet::class);
    }

    public function getExerciseName(): string
    {
        return $this->exercise?->name ?? $this->memberExercise?->name ?? 'Unknown Exercise';
    }
}
