<?php

namespace Rjcodes\Rjcms\Bread\Fields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Rjcodes\Rjcms\Models\BreadField;

/**
 * Defines how a BREAD field type validates and persists its input.
 */
interface FieldContract
{
    /**
     * Selectable options (id => label) for picker-style fields; empty by default.
     *
     * @return Collection<int|string, string>
     */
    public function options(BreadField $field): Collection;

    /**
     * Validation rules for this field, keyed by request input name.
     *
     * @return array<string, mixed>
     */
    public function rules(BreadField $field): array;

    /**
     * Resolve the value to persist on the record from the request.
     */
    public function persistValue(BreadField $field, Request $request, Model $record): mixed;

    /**
     * Whether this field stores its value in a table column. When false, the
     * value is persisted via afterSave() instead (e.g. a pivot relationship).
     */
    public function writesColumn(BreadField $field): bool;

    /**
     * Persist related data after the record has been saved (and has an id).
     */
    public function afterSave(BreadField $field, Request $request, Model $record): void;

    /**
     * The current value used to seed the field's form input.
     */
    public function formValue(BreadField $field, Model $record): mixed;
}
