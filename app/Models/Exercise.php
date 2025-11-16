<?php

namespace App\Models;

use App\Enums\EquipmentType;
use App\Enums\ExerciseCategory;
use App\Enums\ExerciseLevel;
use App\Enums\ExerciseMechanics;
use App\Enums\MuscleGroup;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exercise extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'name',
        'description',
        'category',
        'primary_muscle',
        'secondary_muscles',
        'equipment',
        'equipment_variants',
        'mechanics',
        'level',
        'aliases',
        'wger_id',
        'language',
        'image_url',
        'thumbnail_url',
    ];

    protected function casts(): array
    {
        return [
            'category' => ExerciseCategory::class,
            'primary_muscle' => MuscleGroup::class,
            'secondary_muscles' => 'array',
            'equipment' => EquipmentType::class,
            'equipment_variants' => 'array',
            'mechanics' => ExerciseMechanics::class,
            'level' => ExerciseLevel::class,
            'aliases' => 'array',
        ];
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
