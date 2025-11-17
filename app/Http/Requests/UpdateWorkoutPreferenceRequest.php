<?php

namespace App\Http\Requests;

use App\Enums\CardioType;
use App\Enums\MuscleGroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWorkoutPreferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $preference = $this->route('workoutPreference');

        return $this->user()->id === $preference->user_id
            || ($this->user()->isPt() && $this->user()->memberAssignments()->where('member_id', $preference->user_id)->exists());
    }

    public function rules(): array
    {
        $muscleGroups = array_column(MuscleGroup::cases(), 'value');
        $cardioTypes = array_column(CardioType::cases(), 'value');

        return [
            'weekly_schedule' => ['nullable', 'array'],
            'weekly_schedule.*.focus' => ['nullable', 'string'],
            'weekly_schedule.*.cardio_type' => ['nullable', Rule::in($cardioTypes)],
            'weekly_schedule.*.notes' => ['nullable', 'string', 'max:500'],
            'focus_areas' => ['nullable', 'array'],
            'focus_areas.*' => [Rule::in($muscleGroups)],
            'analysis_window_days' => ['nullable', 'integer', 'min:7', 'max:90'],
            'preferences' => ['nullable', 'array'],
        ];
    }
}
