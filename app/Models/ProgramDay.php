<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramDay extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'program_version_id',
        'day_number',
        'name',
        'description',
        'rest_days_after',
    ];

    protected function casts(): array
    {
        return [
            'day_number' => 'integer',
            'rest_days_after' => 'integer',
        ];
    }

    public function programVersion(): BelongsTo
    {
        return $this->belongsTo(ProgramVersion::class);
    }

    public function exercises(): HasMany
    {
        return $this->hasMany(ProgramDayExercise::class);
    }

    public function sessionPlans(): HasMany
    {
        return $this->hasMany(SessionPlan::class);
    }
}
