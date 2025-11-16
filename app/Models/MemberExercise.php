<?php

namespace App\Models;

use App\Enums\EquipmentType;
use App\Enums\ExerciseCategory;
use App\Enums\ExerciseMechanics;
use App\Enums\MuscleGroup;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MemberExercise extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'category',
        'primary_muscle',
        'secondary_muscles',
        'equipment',
        'mechanics',
    ];

    protected function casts(): array
    {
        return [
            'category' => ExerciseCategory::class,
            'primary_muscle' => MuscleGroup::class,
            'secondary_muscles' => 'array',
            'equipment' => EquipmentType::class,
            'mechanics' => ExerciseMechanics::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function programDayExercises(): HasMany
    {
        return $this->hasMany(ProgramDayExercise::class);
    }

    public function sessionExercises(): HasMany
    {
        return $this->hasMany(SessionExercise::class);
    }
}
