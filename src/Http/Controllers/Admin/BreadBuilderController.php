<?php

namespace Rjcodes\Rjcms\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Rjcodes\Rjcms\Bread\FieldOptions;
use Rjcodes\Rjcms\Bread\SchemaManager;
use Rjcodes\Rjcms\Enums\BreadFieldType;
use Rjcodes\Rjcms\Http\Controllers\Controller;
use Rjcodes\Rjcms\Http\Requests\Admin\BreadRequest;
use Rjcodes\Rjcms\Models\Bread;
use Rjcodes\Rjcms\Models\BreadField;
use Rjcodes\Rjcms\Models\BreadRecord;

/**
 * The visual BREAD builder — manages BREAD definitions and their fields.
 */
class BreadBuilderController extends Controller implements HasMiddleware
{
    /**
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('can:browse_breads', only: ['index']),
            new Middleware('can:create_breads', only: ['create', 'store']),
            new Middleware('can:edit_breads', only: ['edit', 'update']),
            new Middleware('can:delete_breads', only: ['destroy']),
        ];
    }

    /**
     * List every configured BREAD.
     */
    public function index(): View
    {
        return view('rjcms::admin.breads.index', [
            'breads' => Bread::withCount('fields')->orderBy('order')->orderBy('name')->get(),
        ]);
    }

    /**
     * Show the form to register a new BREAD.
     */
    public function create(): View
    {
        return view('rjcms::admin.breads.create', [
            'tables' => Schema::getTableListing(),
            'models' => $this->detectModels(),
        ]);
    }

    /**
     * Register a new BREAD and create its permissions.
     */
    public function store(BreadRequest $request): RedirectResponse
    {
        $bread = Bread::create($request->validated());

        // No-code content types own their schema: stand the table up now so
        // it's ready before any fields are configured.
        if ($bread->usesGenericModel()) {
            app(SchemaManager::class)->ensureTable($bread->table_name);
        }

        $bread->syncPermissions();

        return redirect()
            ->route('admin.breads.edit', $bread)
            ->with('status', 'BREAD created — now configure its fields.');
    }

    /**
     * Open the field builder for a BREAD.
     */
    public function edit(Bread $bread): View
    {
        return view('rjcms::admin.breads.edit', [
            'bread' => $bread,
            'state' => $this->breadState($bread),
            'fieldTypes' => BreadFieldType::cases(),
            // Repeater sub-fields live inside a JSON column, so they can't use
            // the collection-backed library-media types.
            'subfieldTypes' => BreadFieldType::cases(),
            'maxNestDepth' => FieldOptions::MAX_NEST_DEPTH,
            'models' => $this->detectModels(),
        ]);
    }

    /**
     * Persist the BREAD's settings and field configuration.
     */
    public function update(Request $request, Bread $bread): RedirectResponse
    {
        $payload = json_decode((string) $request->input('payload'), true);
        $data = is_array($payload) ? $payload : [];

        // Validate for errors; the full payload (incl. options) is used to persist.
        Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'name_plural' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('breads', 'slug')->ignore($bread)],
            'icon' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'fields' => ['array'],
            // Column names become real SQL identifiers, so constrain them
            // tightly: lowercase, must start with a letter, snake_case only.
            'fields.*.column_name' => ['required', 'string', 'max:255', 'regex:/^[a-z][a-z0-9_]*$/'],
            'fields.*.label' => ['required', 'string', 'max:255'],
            'fields.*.group' => ['nullable', 'string', 'max:255'],
            'fields.*.type' => ['required', Rule::in(array_column(BreadFieldType::cases(), 'value'))],
        ], attributes: [
            'fields.*.column_name' => 'column name',
            'fields.*.label' => 'label',
            'fields.*.group' => 'group',
            'fields.*.type' => 'type',
        ])->validate();

        $bread->update([
            'name' => $data['name'],
            'name_plural' => $data['name_plural'],
            'slug' => $data['slug'],
            'icon' => $data['icon'] ?? null,
            'description' => $data['description'] ?? null,
            'use_permissions' => (bool) ($data['use_permissions'] ?? true),
        ]);

        $bread->fields()->delete();

        foreach (array_values($data['fields'] ?? []) as $order => $field) {
            $bread->fields()->create([
                'column_name' => $field['column_name'],
                'label' => $field['label'],
                'group' => filled($field['group'] ?? null) ? trim($field['group']) : null,
                'type' => $field['type'],
                'required' => (bool) ($field['required'] ?? false),
                'order' => $order,
                'in_browse' => (bool) ($field['in_browse'] ?? false),
                'in_read' => (bool) ($field['in_read'] ?? false),
                'in_edit' => (bool) ($field['in_edit'] ?? false),
                'in_add' => (bool) ($field['in_add'] ?? false),
                'options' => $this->buildOptions($field),
            ]);
        }

        // No-code content types own their schema: create any column a field
        // needs that doesn't exist yet (existing columns are left untouched).
        if ($bread->usesGenericModel()) {
            app(SchemaManager::class)->syncColumns($bread);
        }

        $bread->syncPermissions();

        return redirect()
            ->route('admin.breads.index')
            ->with('status', "BREAD \"{$bread->name_plural}\" saved.");
    }

    /**
     * Delete a BREAD and its fields.
     */
    public function destroy(Bread $bread): RedirectResponse
    {
        $bread->delete();

        return redirect()
            ->route('admin.breads.index')
            ->with('status', 'BREAD deleted.');
    }

    /**
     * The editable client-side state for a BREAD and its fields.
     *
     * @return array<string, mixed>
     */
    private function breadState(Bread $bread): array
    {
        return [
            'name' => $bread->name,
            'name_plural' => $bread->name_plural,
            'slug' => $bread->slug,
            'icon' => (string) $bread->icon,
            'description' => (string) $bread->description,
            'use_permissions' => $bread->isPermissioned(),
            'fields' => $bread->fields->map(fn (BreadField $field) => [
                'uid' => 'f'.$field->id,
                'column_name' => $field->column_name,
                'label' => $field->label,
                'group' => (string) $field->group,
                'type' => $field->type->value,
                'required' => $field->required,
                'in_browse' => $field->in_browse,
                'in_read' => $field->in_read,
                'in_edit' => $field->in_edit,
                'in_add' => $field->in_add,
                'choices' => FieldOptions::choicesToState($field->option('choices', [])),
                'relationship_type' => (string) $field->option('relationship_type', 'belongs_to'),
                'related_model' => (string) $field->option('related_model', ''),
                'display_label' => (string) $field->option('display_label', 'name'),
                'foreign_key' => (string) $field->option('foreign_key', ''),
                'related_key' => (string) $field->option('related_key', 'id'),
                'pivot_table' => (string) $field->option('pivot_table', ''),
                'related_pivot_key' => (string) $field->option('related_pivot_key', ''),
                'taggable' => (bool) $field->option('taggable', false),
                'subfields' => FieldOptions::subfieldsToState($field->option('subfields', []), 0),
            ])->all(),
        ];
    }

    /**
     * Build the type-specific `options` payload for a submitted field.
     *
     * @param  array<string, mixed>  $field
     * @return array<string, mixed>|null
     */
    private function buildOptions(array $field): ?array
    {
        return FieldOptions::build($field);
    }

    /**
     * Discover the Eloquent model classes in app/Models.
     *
     * @return list<string>
     */
    private function detectModels(): array
    {
        return collect(config('rjcms.relationship_models', []))
            ->reject(fn (string $class) => $class === BreadRecord::class)
            ->values()->all();
    }
}
