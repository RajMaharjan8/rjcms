<?php

namespace Rjcodes\Rjcms\Bread\Fields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Rjcodes\Rjcms\Models\BreadField;

/**
 * A repeating group of sub-fields. Sub-field definitions live in the field's
 * `subfields` option and may be any field type — including nested repeaters.
 * Rows are stored as a JSON array, so the model column must be cast to `array`.
 */
class RepeaterField extends BaseField
{
    /**
     * @return array<string, mixed>
     */
    public function rules(BreadField $field): array
    {
        $rules = [$field->column_name => [$this->presence($field), 'array']];

        return $rules + $this->subfieldRules($field->column_name.'.*', $field->option('subfields', []));
    }

    public function persistValue(BreadField $field, Request $request, Model $record): mixed
    {
        $rows = $request->input($field->column_name, []);

        return $this->cleanRows($field->option('subfields', []), is_array($rows) ? $rows : []);
    }

    /**
     * Build validation rules for a row's sub-fields, recursing into nested repeaters.
     *
     * @param  array<int, array<string, mixed>>  $subfields
     * @return array<string, mixed>
     */
    protected function subfieldRules(string $rowPath, array $subfields): array
    {
        $rules = [];

        foreach ($subfields as $sub) {
            $column = $sub['column_name'] ?? null;

            if (! $column) {
                continue;
            }

            $path = "{$rowPath}.{$column}";
            $presence = ($sub['required'] ?? false) ? 'required' : 'nullable';

            switch ($sub['type'] ?? 'text') {
                case 'number':
                    $rules[$path] = [$presence, 'numeric'];
                    break;
                case 'boolean':
                    $rules[$path] = ['nullable', 'boolean'];
                    break;
                case 'date':
                    $rules[$path] = [$presence, 'date'];
                    break;
                case 'image':
                    $rules[$path] = [$presence, 'integer', Rule::exists('medias', 'id')];
                    break;
                case 'images':
                    $rules[$path] = [$presence, 'array'];
                    $rules["{$path}.*"] = ['integer', Rule::exists('medias', 'id')];
                    break;
                case 'select':
                    $rules[$path] = [$presence, Rule::in(array_keys($sub['choices'] ?? []))];
                    break;
                case 'multiselect':
                    $rules[$path] = [$presence, 'array'];
                    $rules["{$path}.*"] = [Rule::in(array_keys($sub['choices'] ?? []))];
                    break;
                case 'repeater':
                    $rules[$path] = [$presence, 'array'];
                    $rules += $this->subfieldRules("{$path}.*", $sub['subfields'] ?? []);
                    break;
                default:
                    $rules[$path] = [$presence, 'string'];
            }
        }

        return $rules;
    }

    /**
     * Coerce each sub-field value by type and drop fully-blank rows, recursing
     * into nested repeaters.
     *
     * @param  array<int, array<string, mixed>>  $subfields
     * @param  array<int, mixed>  $rows
     * @return array<int, array<string, mixed>>
     */
    protected function cleanRows(array $subfields, array $rows): array
    {
        $cleaned = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $out = [];
            $hasValue = false;

            foreach ($subfields as $sub) {
                $column = $sub['column_name'] ?? null;

                if (! $column) {
                    continue;
                }

                $value = $row[$column] ?? null;

                switch ($sub['type'] ?? 'text') {
                    case 'images':
                    case 'multiselect':
                        $value = array_values(array_filter(is_array($value) ? $value : [], fn ($v): bool => filled($v)));
                        $hasValue = $hasValue || ! empty($value);
                        break;
                    case 'repeater':
                        $value = $this->cleanRows($sub['subfields'] ?? [], is_array($value) ? $value : []);
                        $hasValue = $hasValue || ! empty($value);
                        break;
                    case 'boolean':
                        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                        $hasValue = $hasValue || $value;
                        break;
                    default:
                        $hasValue = $hasValue || filled($value);
                }

                $out[$column] = $value;
            }

            if ($hasValue) {
                $cleaned[] = $out;
            }
        }

        return $cleaned;
    }
}
