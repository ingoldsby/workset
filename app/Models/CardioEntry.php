<?php

namespace App\Models;

use App\Enums\CardioType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardioEntry extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'user_id',
        'training_session_id',
        'cardio_type',
        'entry_date',
        'duration_seconds',
        'distance',
        'distance_unit',
        'avg_heart_rate',
        'max_heart_rate',
        'calories_burned',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'cardio_type' => CardioType::class,
            'entry_date' => 'date',
            'duration_seconds' => 'integer',
            'distance' => 'decimal:2',
            'avg_heart_rate' => 'integer',
            'max_heart_rate' => 'integer',
            'calories_burned' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trainingSession(): BelongsTo
    {
        return $this->belongsTo(TrainingSession::class);
    }
}
