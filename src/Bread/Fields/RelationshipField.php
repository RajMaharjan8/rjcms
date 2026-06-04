<?php

namespace Rjcodes\Rjcms\Bread\Fields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Rjcodes\Rjcms\Models\BreadField;

/**
 * A Voyager-style relationship. The definition (type, related model, keys,
 * pivot table) lives in the field's options; the field renders a picker and
 * reads/writes the *real* relational storage — a foreign-key column, the
 * related table's foreign key, or a pivot table — so a normal Eloquent
 * relationship on the model reads the same data on the front end.
 */
class RelationshipField extends BaseField
{
    public const BELONGS_TO = 'belongs_to';

    public const HAS_ONE = 'has_one';

    public const HAS_MANY = 'has_many';

    public const BELONGS_TO_MANY = 'belongs_to_many';

    /**
     * Only belongsTo stores its value in a column on this record's table; the
     * other types write to the related table or a pivot in afterSave().
     */
    public function writesColumn(BreadField $field): bool
    {
        return $this->type($field) === self::BELONGS_TO;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(BreadField $field): array
    {
        $related = $this->relatedInstance($field);
        $exists = $related ? Rule::exists($related->getTable(), $this->relatedKey($field)) : null;

        if ($this->isMultiple($field)) {
            return [
                $field->column_name => [$this->presence($field), 'array'],
                "{$field->column_name}.*" => array_filter(['integer', $exists]),
            ];
        }

        return [$field->column_name => array_filter([$this->presence($field), 'integer', $exists])];
    }

    public function persistValue(BreadField $field, Request $request, Model $record): mixed
    {
        // belongsTo only — keep the existing link when the field isn't submitted.
        if (! $request->has($field->column_name)) {
            return $record->{$field->column_name};
        }

        return $request->input($field->column_name) ?: null;
    }

    public function afterSave(BreadField $field, Request $request, Model $record): void
    {
        $related = $this->relatedInstance($field);

        if ($related === null || $this->type($field) === self::BELONGS_TO) {
            return;
        }

        match ($this->type($field)) {
            self::HAS_ONE => $this->syncHasOne($field, $request, $record, $related),
            self::HAS_MANY => $this->syncHasMany($field, $request, $record, $related),
            self::BELONGS_TO_MANY => $this->syncBelongsToMany($field, $request, $record),
            default => null,
        };
    }

    public function formValue(BreadField $field, Model $record): mixed
    {
        $related = $this->relatedInstance($field);

        if ($related === null) {
            return $this->isMultiple($field) ? [] : null;
        }

        return match ($this->type($field)) {
            self::BELONGS_TO => $record->{$field->column_name},
            self::HAS_ONE => $related->newQuery()
                ->where($this->foreignKey($field), $record->getKey())
                ->value($this->relatedKey($field)),
            self::HAS_MANY => $related->newQuery()
                ->where($this->foreignKey($field), $record->getKey())
                ->pluck($this->relatedKey($field))->all(),
            self::BELONGS_TO_MANY => DB::table($field->option('pivot_table'))
                ->where($this->foreignKey($field), $record->getKey())
                ->pluck($this->relatedPivotKey($field))->all(),
            default => null,
        };
    }

    /**
     * The selectable records for the picker: id => label, ordered by label.
     *
     * @return Collection<int|string, string>
     */
    public function options(BreadField $field): Collection
    {
        $related = $this->relatedInstance($field);

        if ($related === null) {
            return new Collection;
        }

        $label = $this->displayLabel($field);
        $key = $this->relatedKey($field);

        return $related->newQuery()
            ->when($this->hasColumn($related, $label), fn ($query) => $query->orderBy($label))
            ->limit(1000)->get()
            ->mapWithKeys(fn (Model $record): array => [
                $record->{$key} => (string) ($record->{$label} ?? "#{$record->{$key}}"),
            ]);
    }

    /**
     * Whether this relationship selects multiple records.
     */
    public function isMultiple(BreadField $field): bool
    {
        return in_array($this->type($field), [self::HAS_MANY, self::BELONGS_TO_MANY], true);
    }

    private function type(BreadField $field): string
    {
        return $field->option('relationship_type', self::BELONGS_TO);
    }

    private function relatedInstance(BreadField $field): ?Model
    {
        $class = $field->option('related_model');

        return is_string($class) && is_subclass_of($class, Model::class) ? new $class : null;
    }

    private function displayLabel(BreadField $field): string
    {
        return $field->option('display_label') ?: 'name';
    }

    private function relatedKey(BreadField $field): string
    {
        return $field->option('related_key') ?: 'id';
    }

    /**
     * The related model's key column on the pivot, defaulting to Laravel's
     * convention: snake(class basename) . '_id' (e.g. tour_id).
     */
    private function relatedPivotKey(BreadField $field): string
    {
        if (filled($explicit = $field->option('related_pivot_key'))) {
            return $explicit;
        }

        $class = $field->option('related_model');

        return $class ? Str::snake(class_basename($class)).'_id' : 'id';
    }

    /**
     * The foreign key that references *this* record — the column on the related
     * table (has-one/many) or this record's key on the pivot (belongs-to-many).
     * Defaults to the convention: snake(this model/table singular) . '_id'.
     */
    private function foreignKey(BreadField $field): string
    {
        if (filled($explicit = $field->option('foreign_key'))) {
            return $explicit;
        }

        $bread = $field->bread;
        $base = $bread?->model ? class_basename($bread->model) : Str::singular((string) $bread?->table_name);

        return $base ? Str::snake($base).'_id' : '';
    }

    private function hasColumn(Model $model, string $column): bool
    {
        return $model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), $column);
    }

    private function syncHasOne(BreadField $field, Request $request, Model $record, Model $related): void
    {
        $chosen = $request->input($field->column_name) ?: null;
        $fk = $this->foreignKey($field);

        $related->newQuery()->where($fk, $record->getKey())->update([$fk => null]);

        if ($chosen !== null) {
            $related->newQuery()->where($this->relatedKey($field), $chosen)->update([$fk => $record->getKey()]);
        }
    }

    private function syncHasMany(BreadField $field, Request $request, Model $record, Model $related): void
    {
        $ids = array_values((array) $request->input($field->column_name, []));
        $fk = $this->foreignKey($field);
        $key = $this->relatedKey($field);

        // Detach rows no longer selected, then attach the chosen ones.
        $related->newQuery()->where($fk, $record->getKey())->whereNotIn($key, $ids ?: [0])->update([$fk => null]);

        if ($ids !== []) {
            $related->newQuery()->whereIn($key, $ids)->update([$fk => $record->getKey()]);
        }
    }

    private function syncBelongsToMany(BreadField $field, Request $request, Model $record): void
    {
        $ids = array_values((array) $request->input($field->column_name, []));
        $pivot = $field->option('pivot_table');
        $foreignKey = $this->foreignKey($field);
        $relatedKey = $this->relatedPivotKey($field);

        DB::table($pivot)->where($foreignKey, $record->getKey())->delete();

        if ($ids !== []) {
            DB::table($pivot)->insert(array_map(fn ($id): array => [
                $foreignKey => $record->getKey(),
                $relatedKey => $id,
            ], $ids));
        }
    }
}
