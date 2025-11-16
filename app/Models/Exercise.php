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
use Laravel\Scout\Searchable;

class Exercise extends Model
{
    use HasFactory;
    use HasUlids;
    use Searchable;

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

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category?->value,
            'primary_muscle' => $this->primary_muscle?->value,
            'equipment' => $this->equipment?->value,
            'level' => $this->level?->value,
            'aliases' => $this->aliases,
        ];
    }
}
