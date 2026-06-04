<?php

namespace Rjcodes\Rjcms\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Rjcodes\Rjcms\Bread\FieldOptions;
use Rjcodes\Rjcms\Enums\BreadFieldType;
use Rjcodes\Rjcms\Http\Controllers\Controller;
use Rjcodes\Rjcms\Models\CustomField;

/**
 * Manages the typed custom-field definitions attached to a built-in content
 * type (Blogs). Values are stored in each record's JSON meta column.
 */
class CustomFieldController extends Controller
{
    /** Content types that support custom fields, with their singular labels. */
    private const GROUPS = ['blogs' => 'Blog'];

    public function index(string $group): View
    {
        $this->authorizeGroup($group);

        return view('rjcms::admin.custom-fields.index', [
            'group' => $group,
            'groupLabel' => self::GROUPS[$group],
            'fields' => CustomField::forGroup($group),
        ]);
    }

    public function create(string $group): View
    {
        $this->authorizeGroup($group);

        return view('rjcms::admin.custom-fields.create', $this->formData($group, [
            'key' => '', 'label' => '', 'type' => 'text', 'required' => false, 'order' => 0,
            'choices' => [], 'subfields' => [],
        ]));
    }

    public function store(Request $request, string $group): RedirectResponse
    {
        $this->authorizeGroup($group);
        $data = $this->validatedDefinition($request, $group);

        CustomField::create([
            'group' => $group,
            'key' => $data['key'],
            'label' => $data['label'],
            'type' => $data['type'],
            'required' => $data['required'],
            'order' => $data['order'],
            'options' => FieldOptions::build($data),
        ]);

        return redirect()->route('admin.content-fields.index', $group)->with('status', "Field \"{$data['label']}\" added.");
    }

    public function edit(string $group, CustomField $field): View
    {
        $this->authorizeGroup($group, $field);

        return view('rjcms::admin.custom-fields.edit', $this->formData($group, [
            'key' => $field->key,
            'label' => $field->label,
            'type' => $field->type,
            'required' => (bool) $field->required,
            'order' => $field->order,
            'choices' => FieldOptions::choicesToState($field->options['choices'] ?? []),
            'subfields' => FieldOptions::subfieldsToState($field->options['subfields'] ?? [], 0),
        ], $field));
    }

    public function update(Request $request, string $group, CustomField $field): RedirectResponse
    {
        $this->authorizeGroup($group, $field);
        $data = $this->validatedDefinition($request, $group, $field);

        $field->update([
            'key' => $data['key'],
            'label' => $data['label'],
            'type' => $data['type'],
            'required' => $data['required'],
            'order' => $data['order'],
            'options' => FieldOptions::build($data),
        ]);

        return redirect()->route('admin.content-fields.index', $group)->with('status', "Field \"{$data['label']}\" updated.");
    }

    public function destroy(string $group, CustomField $field): RedirectResponse
    {
        $this->authorizeGroup($group, $field);

        $field->delete();

        return redirect()->route('admin.content-fields.index', $group)->with('status', 'Field removed. Existing stored values are kept.');
    }

    /**
     * Authorize managing fields for a group, ensuring the field belongs to it.
     */
    private function authorizeGroup(string $group, ?CustomField $field = null): void
    {
        abort_unless(array_key_exists($group, self::GROUPS), 404);
        abort_if($field && $field->group !== $group, 404);

        Gate::authorize("edit_{$group}");
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    private function formData(string $group, array $state, ?CustomField $field = null): array
    {
        return [
            'group' => $group,
            'groupLabel' => self::GROUPS[$group],
            'field' => $field,
            'state' => $state,
            // Library-media types attach to collections, which custom fields
            // (stored in the record's meta JSON) don't support.
            'fieldTypes' => BreadFieldType::cases(),
            'maxNestDepth' => FieldOptions::MAX_NEST_DEPTH,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedDefinition(Request $request, string $group, ?CustomField $field = null): array
    {
        $payload = json_decode((string) $request->input('payload'), true);
        $data = is_array($payload) ? $payload : [];

        Validator::make($data, [
            'key' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9_]+$/', Rule::unique('custom_fields', 'key')->where('group', $group)->ignore($field?->id)],
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_column(BreadFieldType::cases(), 'value'))],
            'order' => ['nullable', 'integer'],
        ], attributes: ['key' => 'key'])->validate();

        $data['required'] = (bool) ($data['required'] ?? false);
        $data['order'] = (int) ($data['order'] ?? 0);

        return $data;
    }
}
