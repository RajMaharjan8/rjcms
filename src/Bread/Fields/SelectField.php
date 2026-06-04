<?php

namespace Rjcodes\Rjcms\Bread\Fields;

use Illuminate\Validation\Rule;
use Rjcodes\Rjcms\Models\BreadField;

/**
 * A single-choice dropdown. Choices come from the field's `choices` option
 * (a map of stored value => display label).
 */
class SelectField extends BaseField
{
    /**
     * @return array<string, mixed>
     */
    public function rules(BreadField $field): array
    {
        $choices = array_keys($field->option('choices', []));

        return [$field->column_name => [$this->presence($field), Rule::in($choices)]];
    }
}
