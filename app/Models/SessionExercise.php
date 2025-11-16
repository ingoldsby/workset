<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SessionExercise extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'training_session_id',
        'exercise_id',
        'member_exercise_id',
        'order',
        'superset_group',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    public function trainingSession(): BelongsTo
    {
        return $this->belongsTo(TrainingSession::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function memberExercise(): BelongsTo
    {
        return $this->belongsTo(MemberExercise::class);
    }

    public function sets(): HasMany
    {
        return $this->hasMany(SessionSet::class);
    }

    public function getExerciseName(): string
    {
        return $this->exercise?->name ?? $this->memberExercise?->name ?? 'Unknown Exercise';
    }
}
