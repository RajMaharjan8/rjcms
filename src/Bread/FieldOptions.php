<?php

namespace Rjcodes\Rjcms\Bread;

/**
 * Builds the type-specific `options` payload (select/multi-select choices and
 * repeater sub-fields) from a submitted field definition. Shared by the BREAD
 * builder and the Settings module so both behave identically.
 */
class FieldOptions
{
    /**
     * Maximum nesting depth for repeater sub-fields.
     */
    public const MAX_NEST_DEPTH = 2;

    /**
     * @param  array<string, mixed>  $field
     * @return array<string, mixed>|null
     */
    public static function build(array $field): ?array
    {
        return match ($field['type'] ?? null) {
            'select', 'multiselect' => ['choices' => self::choices($field['choices'] ?? [])],
            'relationship' => [
                'relationship_type' => $field['relationship_type'] ?? 'belongs_to',
                'related_model' => $field['related_model'] ?? null,
                'display_label' => ($field['display_label'] ?? '') ?: 'name',
                'foreign_key' => $field['foreign_key'] ?? null,
                'related_key' => ($field['related_key'] ?? '') ?: 'id',
                'pivot_table' => $field['pivot_table'] ?? null,
                'related_pivot_key' => $field['related_pivot_key'] ?? null,
                'taggable' => (bool) ($field['taggable'] ?? false),
            ],
            'repeater' => ['subfields' => self::subfields($field['subfields'] ?? [], 0)],
            default => null,
        };
    }

    /**
     * @param  array<int, array<string, mixed>>  $choices
     * @return array<string, string>
     */
    public static function choices(array $choices): array
    {
        return collect($choices)
            ->filter(fn ($choice) => filled($choice['key'] ?? null))
            ->mapWithKeys(fn ($choice) => [$choice['key'] => ($choice['label'] ?? '') ?: $choice['key']])
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $subfields
     * @return array<int, array<string, mixed>>
     */
    public static function subfields(array $subfields, int $depth): array
    {
        return collect($subfields)
            ->filter(fn ($sub) => filled($sub['column_name'] ?? null))
            ->map(function ($sub) use ($depth): array {
                $type = $sub['type'] ?? 'text';

                $entry = [
                    'column_name' => $sub['column_name'],
                    'label' => ($sub['label'] ?? '') ?: $sub['column_name'],
                    'type' => $type,
                    'required' => (bool) ($sub['required'] ?? false),
                ];

                if (in_array($type, ['select', 'multiselect'], true)) {
                    $entry['choices'] = self::choices($sub['choices'] ?? []);
                }

                if ($type === 'repeater') {
                    $entry['subfields'] = $depth < self::MAX_NEST_DEPTH
                        ? self::subfields($sub['subfields'] ?? [], $depth + 1)
                        : [];
                }

                return $entry;
            })->values()->all();
    }

    /**
     * Map stored sub-field definitions into editable client-side state.
     *
     * @param  array<int, array<string, mixed>>  $subfields
     * @return array<int, array<string, mixed>>
     */
    public static function subfieldsToState(array $subfields, int $depth): array
    {
        return collect($subfields)->map(fn ($sub) => [
            'column_name' => $sub['column_name'] ?? '',
            'label' => $sub['label'] ?? '',
            'type' => $sub['type'] ?? 'text',
            'required' => (bool) ($sub['required'] ?? false),
            'choices' => self::choicesToState($sub['choices'] ?? []),
            'subfields' => $depth < self::MAX_NEST_DEPTH
                ? self::subfieldsToState($sub['subfields'] ?? [], $depth + 1)
                : [],
        ])->values()->all();
    }

    /**
     * @param  array<string, string>  $choices
     * @return array<int, array{key: string, label: string}>
     */
    public static function choicesToState(array $choices): array
    {
        return collect($choices)
            ->map(fn ($label, $key) => ['key' => (string) $key, 'label' => (string) $label])
            ->values()->all();
    }
}
