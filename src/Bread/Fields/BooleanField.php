<?php

namespace Rjcodes\Rjcms\Bread\Fields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Rjcodes\Rjcms\Models\BreadField;

/**
 * A checkbox storing a true/false value.
 */
class BooleanField extends BaseField
{
    /**
     * @return array<string, mixed>
     */
    public function rules(BreadField $field): array
    {
        return [$field->column_name => ['nullable', 'boolean']];
    }

    public function persistValue(BreadField $field, Request $request, Model $record): mixed
    {
        return $request->boolean($field->column_name);
    }
}
