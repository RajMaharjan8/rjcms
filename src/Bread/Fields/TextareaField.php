<?php

namespace Rjcodes\Rjcms\Bread\Fields;

use Rjcodes\Rjcms\Models\BreadField;

/**
 * A multi-line text area.
 */
class TextareaField extends BaseField
{
    /**
     * @return array<string, mixed>
     */
    public function rules(BreadField $field): array
    {
        return [$field->column_name => [$this->presence($field), 'string']];
    }
}
