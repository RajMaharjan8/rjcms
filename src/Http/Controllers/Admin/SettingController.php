<?php

namespace Rjcodes\Rjcms\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Rjcodes\Rjcms\Bread\FieldOptions;
use Rjcodes\Rjcms\Enums\BreadFieldType;
use Rjcodes\Rjcms\Http\Controllers\Controller;
use Rjcodes\Rjcms\Models\Setting;

/**
 * Voyager-style site settings: typed, grouped key/value pairs that reuse the
 * BREAD field types for rendering, validation, and persistence.
 */
class SettingController extends Controller implements HasMiddleware
{
    /**
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('can:browse_settings', only: ['index']),
            new Middleware('can:edit_settings', only: ['save', 'edit', 'update']),
            new Middleware('can:create_settings', only: ['create', 'store']),
            new Middleware('can:delete_settings', only: ['destroy']),
        ];
    }

    /**
     * Show all settings, grouped into category tabs, with an editable value form.
     */
    public function index(): View
    {
        return view('rjcms::admin.settings.index', [
            'groups' => $this->orderedSettings()->groupBy('group'),
        ]);
    }

    /**
     * Persist the submitted value for every setting.
     */
    public function save(Request $request): RedirectResponse
    {
        $settings = $this->orderedSettings();

        $rules = [];
        $fields = [];
        foreach ($settings as $setting) {
            $field = $setting->asField();
            $fields[$setting->id] = $field;
            $rules += $field->handler()->rules($field);
        }

        $request->validate($rules);

        foreach ($settings as $setting) {
            $field = $fields[$setting->id];
            $setting->storeValue($field->handler()->persistValue($field, $request, $setting));
        }

        return redirect()
            ->route('admin.settings.index')
            ->with('status', 'Settings saved.');
    }

    /**
     * Show the form to define a new setting.
     */
    public function create(): View
    {
        return view('rjcms::admin.settings.create', $this->definitionViewData());
    }

    /**
     * Store a new setting definition.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedDefinition($request);

        Setting::create([
            'group' => $data['group'],
            'key' => $data['key'],
            'display_name' => $data['display_name'],
            'type' => $data['type'],
            'required' => $data['required'],
            'order' => $data['order'],
            'options' => FieldOptions::build($data),
        ]);

        return redirect()
            ->route('admin.settings.index')
            ->with('status', "Setting \"{$data['display_name']}\" created.");
    }

    /**
     * Show the form to edit a setting definition.
     */
    public function edit(Setting $setting): View
    {
        return view('rjcms::admin.settings.edit', [
            ...$this->definitionViewData(),
            'setting' => $setting,
            'state' => [
                'key' => $setting->key,
                'display_name' => $setting->display_name,
                'group' => $setting->group,
                'type' => $setting->type,
                'required' => (bool) $setting->required,
                'order' => $setting->order,
                'choices' => FieldOptions::choicesToState($setting->options['choices'] ?? []),
                'subfields' => FieldOptions::subfieldsToState($setting->options['subfields'] ?? [], 0),
            ],
        ]);
    }

    /**
     * Update a setting definition (the stored value is preserved).
     */
    public function update(Request $request, Setting $setting): RedirectResponse
    {
        $data = $this->validatedDefinition($request, $setting);

        $setting->update([
            'group' => $data['group'],
            'key' => $data['key'],
            'display_name' => $data['display_name'],
            'type' => $data['type'],
            'required' => $data['required'],
            'order' => $data['order'],
            'options' => FieldOptions::build($data),
        ]);

        return redirect()
            ->route('admin.settings.index')
            ->with('status', "Setting \"{$data['display_name']}\" updated.");
    }

    /**
     * Delete a setting.
     */
    public function destroy(Setting $setting): RedirectResponse
    {
        if ($setting->isLocked()) {
            return redirect()
                ->route('admin.settings.index')
                ->with('error', 'This setting is required and cannot be deleted.');
        }

        $setting->delete();

        return redirect()
            ->route('admin.settings.index')
            ->with('status', 'Setting deleted.');
    }

    /**
     * Settings ordered for display.
     *
     * @return Collection<int, Setting>
     */
    private function orderedSettings()
    {
        return Setting::orderBy('group')->orderBy('order')->orderBy('id')->get();
    }

    /**
     * Shared data for the create/edit definition forms.
     *
     * @return array<string, mixed>
     */
    private function definitionViewData(): array
    {
        return [
            // Library-media types attach to collections, which a setting value
            // (a single stored value) doesn't support.
            'fieldTypes' => BreadFieldType::cases(),
            'maxNestDepth' => FieldOptions::MAX_NEST_DEPTH,
            'groups' => Setting::query()->distinct()->orderBy('group')->pluck('group')->all(),
        ];
    }

    /**
     * Decode and validate a submitted setting-definition payload.
     *
     * @return array<string, mixed>
     */
    private function validatedDefinition(Request $request, ?Setting $setting = null): array
    {
        $payload = json_decode((string) $request->input('payload'), true);
        $data = is_array($payload) ? $payload : [];

        Validator::make($data, [
            'key' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique('settings', 'key')->ignore($setting)],
            'display_name' => ['required', 'string', 'max:255'],
            'group' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_column(BreadFieldType::cases(), 'value'))],
            'order' => ['nullable', 'integer'],
        ], attributes: [
            'key' => 'key',
            'display_name' => 'display name',
        ])->validate();

        $data['required'] = (bool) ($data['required'] ?? false);
        $data['order'] = (int) ($data['order'] ?? 0);

        return $data;
    }
}
