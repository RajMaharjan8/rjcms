<?php

namespace Rjcodes\Rjcms\Bread\Fields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Rjcodes\Rjcms\Models\BreadField;

/**
 * Sensible defaults shared by every field type handler.
 */
abstract class BaseField implements FieldContract
{
    /**
     * @return Collection<int|string, string>
     */
    public function options(BreadField $field): Collection
    {
        return new Collection;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(BreadField $field): array
    {
        return [$field->column_name => [$this->presence($field)]];
    }

    public function persistValue(BreadField $field, Request $request, Model $record): mixed
    {
        return $request->input($field->column_name);
    }

    public function writesColumn(BreadField $field): bool
    {
        return true;
    }

    public function afterSave(BreadField $field, Request $request, Model $record): void {}

    public function formValue(BreadField $field, Model $record): mixed
    {
        return $record->{$field->column_name} ?? null;
    }

    /**
     * The required/nullable rule based on the field configuration.
     */
    protected function presence(BreadField $field): string
    {
        return $field->required ? 'required' : 'nullable';
    }
}
