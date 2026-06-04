<?php

namespace Rjcodes\Rjcms\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Rjcodes\Rjcms\Models\Media;

class MediaRequest extends FormRequest
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
        $isCreating = ! $this->route('medium') instanceof Media;

        return [
            'name' => [$isCreating ? 'nullable' : 'required', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'file' => [
                $isCreating ? 'required' : 'prohibited',
                'image',
                'mimes:jpg,jpeg,png,webp,gif,svg',
                'max:5120',
            ],
        ];
    }
}
