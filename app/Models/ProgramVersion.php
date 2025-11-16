<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramVersion extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'program_id',
        'created_by',
        'version_number',
        'change_notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'version_number' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function days(): HasMany
    {
        return $this->hasMany(ProgramDay::class);
    }

    public function sessionPlans(): HasMany
    {
        return $this->hasMany(SessionPlan::class, 'program_day_id');
    }
}
