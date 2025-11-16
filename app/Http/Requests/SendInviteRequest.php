<?php

namespace App\Http\Requests;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendInviteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role->canInvite();
    }

    public function rules(): array
    {
        $rules = [
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
                Rule::unique('invites', 'email')->where(function ($query) {
                    return $query->whereNull('accepted_at')
                        ->where('expires_at', '>', now());
                }),
            ],
            'role' => ['required', Rule::enum(Role::class)],
        ];

        // PT can only be assigned when inviting a member
        if ($this->input('role') === Role::Member->value) {
            $rules['pt_id'] = [
                'nullable',
                'string',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('role', Role::PT->value);
                }),
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'This email is already registered or has a pending invitation.',
            'pt_id.exists' => 'The selected personal trainer does not exist.',
        ];
    }
}
