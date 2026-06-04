<?php

namespace Rjcodes\Rjcms\Bread\Fields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Rjcodes\Rjcms\Models\BreadField;
use Rjcodes\Rjcms\Models\Media;

/**
 * A single image chosen from the media library. The media picker submits the
 * selected media id, so only that id is validated and persisted on the record.
 */
class ImageField extends BaseField
{
    /**
     * @return array<string, mixed>
     */
    public function rules(BreadField $field): array
    {
        return [$field->column_name => [$this->presence($field), 'integer', Rule::exists('medias', 'id')]];
    }

    public function persistValue(BreadField $field, Request $request, Model $record): mixed
    {
        // Field omitted entirely (e.g. partial update): keep the existing id.
        if (! $request->has($field->column_name)) {
            return $record->{$field->column_name};
        }

        // Present but empty: the picker cleared the selection.
        return $request->input($field->column_name) ?: null;
    }

    /**
     * The media picker and read views work with the raw id, while the column
     * casts to a Media model for the front end — so unwrap it back to an id here.
     */
    public function formValue(BreadField $field, Model $record): mixed
    {
        $value = $record->{$field->column_name};

        return $value instanceof Media ? $value->id : $value;
    }
}
