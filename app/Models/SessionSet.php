<?php

namespace App\Models;

use App\Enums\SetType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionSet extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'session_exercise_id',
        'set_number',
        'set_type',
        'prescribed_reps',
        'prescribed_weight',
        'prescribed_rpe',
        'performed_reps',
        'performed_weight',
        'performed_rpe',
        'time_seconds',
        'tempo',
        'completed',
        'completed_as_prescribed',
        'skipped',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'set_number' => 'integer',
            'set_type' => SetType::class,
            'prescribed_reps' => 'integer',
            'prescribed_weight' => 'decimal:2',
            'prescribed_rpe' => 'integer',
            'performed_reps' => 'integer',
            'performed_weight' => 'decimal:2',
            'performed_rpe' => 'integer',
            'time_seconds' => 'integer',
            'completed' => 'boolean',
            'completed_as_prescribed' => 'boolean',
            'skipped' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    public function sessionExercise(): BelongsTo
    {
        return $this->belongsTo(SessionExercise::class);
    }

    public function isCompleted(): bool
    {
        return $this->completed && ! $this->skipped;
    }

    public function wasSkipped(): bool
    {
        return $this->skipped;
    }

    public function completedAsPrescribed(): bool
    {
        return $this->completed_as_prescribed;
    }
}
