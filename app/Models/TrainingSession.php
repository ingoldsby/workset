<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingSession extends Model
{
    use HasFactory;
    use HasUlids;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'session_plan_id',
        'logged_by',
        'scheduled_date',
        'started_at',
        'completed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sessionPlan(): BelongsTo
    {
        return $this->belongsTo(SessionPlan::class);
    }

    public function logger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_by');
    }

    public function exercises(): HasMany
    {
        return $this->hasMany(SessionExercise::class);
    }

    public function cardioEntries(): HasMany
    {
        return $this->hasMany(CardioEntry::class);
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    public function isInProgress(): bool
    {
        return $this->started_at !== null && $this->completed_at === null;
    }

    public function isPending(): bool
    {
        return $this->started_at === null;
    }
}
