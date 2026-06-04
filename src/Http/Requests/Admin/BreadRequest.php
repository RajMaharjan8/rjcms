<?php

namespace Rjcodes\Rjcms\Http\Requests\Admin;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BreadRequest extends FormRequest
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
     * Normalize the permissions checkbox to a boolean before validation.
     */
    protected function prepareForValidation(): void
    {
        // Default to creating permissions when the checkbox is absent entirely;
        // honour an explicit "0" (unchecked) from the form.
        $this->merge([
            'use_permissions' => $this->has('use_permissions') ? $this->boolean('use_permissions') : true,
        ]);

        // No table given? It's a no-code content type — derive a snake_case
        // table name from the slug so the user never has to pick one.
        if (! $this->filled('table_name') && $this->filled('slug')) {
            $this->merge(['table_name' => Str::snake(str_replace('-', '_', (string) $this->input('slug')))]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'name_plural' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('breads', 'slug')],
            // Optional: leave blank for a no-code content type (backed by the
            // generic BreadRecord). If given, the class must exist.
            'model' => [
                'nullable', 'string',
                fn (string $attribute, mixed $value, Closure $fail) => $value === null || $value === '' || class_exists($value)
                    ? null
                    : $fail('The model class does not exist.'),
            ],
            // The table need not exist — the builder creates it automatically.
            // Constrain it to a safe SQL identifier since it names a real table.
            'table_name' => ['required', 'string', 'max:255', 'regex:/^[a-z][a-z0-9_]*$/'],
            'icon' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'use_permissions' => ['boolean'],
        ];
    }
}
