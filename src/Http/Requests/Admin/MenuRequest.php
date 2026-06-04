<?php

namespace Rjcodes\Rjcms\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Rjcodes\Rjcms\Models\Menu;

class MenuRequest extends FormRequest
{
    /**
     * Route-level permission middleware already gates this action.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Derive a slug from the name when one wasn't provided.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->filled('slug') && $this->filled('name')) {
            $this->merge(['slug' => str($this->input('name'))->slug()->value()]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $menu = $this->route('menu');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required', 'string', 'max:255', 'alpha_dash',
                Rule::unique('menus', 'slug')->ignore($menu instanceof Menu ? $menu->id : null),
            ],
        ];
    }
}
