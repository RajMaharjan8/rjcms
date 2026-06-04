<?php

namespace Rjcodes\Rjcms\Bread\Fields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Rjcodes\Rjcms\Models\BreadField;
use Rjcodes\Rjcms\Models\Media;

/**
 * Multiple images chosen from the media library. The picker submits an ordered
 * array of media ids, stored as JSON — so the model column must cast to `array`.
 */
class ImagesField extends BaseField
{
    /**
     * @return array<string, mixed>
     */
    public function rules(BreadField $field): array
    {
        return [
            $field->column_name => [$this->presence($field), 'array'],
            "{$field->column_name}.*" => ['integer', Rule::exists('medias', 'id')],
        ];
    }

    public function persistValue(BreadField $field, Request $request, Model $record): mixed
    {
        $value = $request->input($field->column_name, []);

        return is_array($value) ? array_values($value) : [];
    }

    /**
     * The media picker and read views work with raw ids, while the column casts
     * to a Media collection for the front end — so unwrap back to ids here.
     *
     * @return list<int>
     */
    public function formValue(BreadField $field, Model $record): array
    {
        return collect($record->{$field->column_name})
            ->map(fn ($item): int => $item instanceof Media ? $item->id : (int) $item)
            ->values()
            ->all();
    }
}
