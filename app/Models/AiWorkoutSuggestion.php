<?php

namespace App\Models;

use App\Enums\SuggestionType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiWorkoutSuggestion extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'user_id',
        'generated_by',
        'suggestion_type',
        'prompt_context',
        'suggestion_data',
        'analysis_data',
        'applied_to_session_id',
        'applied_to_program_id',
        'applied_at',
    ];

    protected function casts(): array
    {
        return [
            'suggestion_type' => SuggestionType::class,
            'prompt_context' => 'array',
            'suggestion_data' => 'array',
            'analysis_data' => 'array',
            'applied_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function appliedToSession(): BelongsTo
    {
        return $this->belongsTo(TrainingSession::class, 'applied_to_session_id');
    }

    public function appliedToProgram(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'applied_to_program_id');
    }

    public function isApplied(): bool
    {
        return $this->applied_at !== null;
    }

    public function markAsApplied(?string $sessionId = null, ?string $programId = null): void
    {
        $this->update([
            'applied_to_session_id' => $sessionId,
            'applied_to_program_id' => $programId,
            'applied_at' => now(),
        ]);
    }
}
