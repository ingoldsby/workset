<?php

namespace App\Http\Requests;

use App\Enums\SuggestionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerateWorkoutSuggestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (! $this->user()->isPt()) {
            return false;
        }

        $memberId = $this->input('user_id');

        return $this->user()->memberAssignments()
            ->where('member_id', $memberId)
            ->exists();
    }

    public function rules(): array
    {
        $suggestionTypes = array_column(SuggestionType::cases(), 'value');

        return [
            'user_id' => ['required', 'exists:users,id'],
            'suggestion_type' => ['required', Rule::in($suggestionTypes)],
            'custom_prompt' => ['nullable', 'string', 'max:1000'],
            'analysis_window_days' => ['nullable', 'integer', 'min:7', 'max:90'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Please specify which member this suggestion is for.',
            'user_id.exists' => 'The specified member does not exist.',
            'suggestion_type.required' => 'Please specify the type of suggestion to generate.',
            'suggestion_type.in' => 'Invalid suggestion type. Must be one of: single_session, exercise_list, weekly_program.',
        ];
    }
}
