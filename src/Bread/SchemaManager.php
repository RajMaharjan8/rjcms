<?php

namespace Rjcodes\Rjcms\Bread;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Rjcodes\Rjcms\Models\Bread;

/**
 * Keeps a BREAD's underlying table in sync with its field definitions so a
 * content type can be built entirely from the admin UI — no migrations.
 *
 * Sync is strictly additive: missing columns are created, but existing
 * columns are never altered or dropped, so configured data is safe.
 */
class SchemaManager
{
    /**
     * Resolved casts per table, memoised for the lifetime of the request.
     *
     * @var array<string, array<string, string>>
     */
    private static array $castCache = [];

    /**
     * Columns the engine owns implicitly and must never treat as a field.
     *
     * @var list<string>
     */
    private const RESERVED = ['id', 'created_at', 'updated_at'];

    /**
     * Create the table (with id + timestamps) if it does not exist yet.
     */
    public function ensureTable(string $table): void
    {
        if (! Schema::hasTable($table)) {
            Schema::create($table, function (Blueprint $blueprint): void {
                $blueprint->id();
                $blueprint->timestamps();
            });
        }
    }

    /**
     * Ensure the BREAD's table exists and has a column for every field that
     * stores its value in a column. Existing columns are left untouched.
     */
    public function syncColumns(Bread $bread): void
    {
        $table = $bread->table_name;
        $this->ensureTable($table);

        $fields = $bread->fields()->get();

        Schema::table($table, function (Blueprint $blueprint) use ($fields, $table): void {
            foreach ($fields as $field) {
                $name = $field->column_name;

                if (in_array($name, self::RESERVED, true) || ! $field->handler()->writesColumn($field)) {
                    continue;
                }

                if (! Schema::hasColumn($table, $name)) {
                    $field->type->defineColumn($blueprint, $name);
                }
            }
        });

        unset(self::$castCache[$table]);
    }

    /**
     * The attribute casts a generic record should apply, derived from the
     * BREAD bound to the given table.
     *
     * @return array<string, string>
     */
    public static function castsFor(string $table): array
    {
        if ($table === '') {
            return [];
        }

        return self::$castCache[$table] ??= self::resolveCasts($table);
    }

    /**
     * @return array<string, string>
     */
    private static function resolveCasts(string $table): array
    {
        if (! Schema::hasTable('breads') || ! Schema::hasTable('bread_fields')) {
            return [];
        }

        $bread = Bread::where('table_name', $table)->with('fields')->first();

        if ($bread === null) {
            return [];
        }

        $casts = [];

        foreach ($bread->fields as $field) {
            if (($cast = $field->type->castType()) !== null) {
                $casts[$field->column_name] = $cast;
            }
        }

        return $casts;
    }
}
