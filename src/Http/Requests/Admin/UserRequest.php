<?php

namespace Rjcodes\Rjcms\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Rjcodes\Rjcms\Models\User;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Route-level permission middleware already gates this action.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $user = $this->route('user');
        $isCreating = ! $user instanceof User;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'lowercase', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($user),
            ],
            'password' => [
                $isCreating ? 'required' : 'nullable',
                'confirmed',
                Password::defaults(),
            ],
            'roles' => ['array'],
            'roles.*' => [Rule::exists('roles', 'name')],
        ];
    }
}
