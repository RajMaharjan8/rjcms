<?php

namespace Rjcodes\Rjcms\Bread\Fields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Rjcodes\Rjcms\Models\BreadField;

/**
 * A multiple-choice field. The selected values are stored as a JSON array,
 * so the model column must be cast to `array`.
 */
class MultiSelectField extends BaseField
{
    /**
     * @return array<string, mixed>
     */
    public function rules(BreadField $field): array
    {
        $choices = array_keys($field->option('choices', []));

        return [
            $field->column_name => [$this->presence($field), 'array'],
            "{$field->column_name}.*" => [Rule::in($choices)],
        ];
    }

    public function persistValue(BreadField $field, Request $request, Model $record): mixed
    {
        return $request->input($field->column_name, []);
    }
}
