<?php

namespace Rjcodes\Rjcms\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Rjcodes\Rjcms\Http\Controllers\Controller;
use Rjcodes\Rjcms\Models\CustomField;

/**
 * Shared admin CRUD for the built-in publishable content types (Pages, Blogs).
 * Each has the same fixed fields — title, slug, body, featured image, and a
 * draft/published status — so the behaviour lives here and subclasses just
 * declare their model and identifiers.
 */
abstract class PublishableController extends Controller
{
    /** The route/permission key, e.g. "pages" or "blogs". */
    abstract protected function key(): string;

    /** Singular human label, e.g. "Page" or "Blog". */
    abstract protected function label(): string;

    /** The underlying database table (for unique slug validation). */
    abstract protected function table(): string;

    /** A fresh model instance. */
    abstract protected function newModel(): Model;

    /**
     * A base query for the content type.
     *
     * @return Builder<covariant Model>
     */
    abstract protected function baseQuery(): Builder;

    /**
     * Public front-end route name for a record (e.g. "pages.show").
     */
    abstract protected function publicRoute(): string;

    public function index(): View
    {
        $this->gate('browse');

        $perPage = max(1, min(request()->integer('perPage', 15), 100));

        return view('rjcms::admin.publishable.index', $this->viewData([
            'records' => $this->baseQuery()->latest()->paginate($perPage),
        ]));
    }

    public function create(): View
    {
        $this->gate('create');

        return view('rjcms::admin.publishable.create', $this->viewData(['record' => $this->newModel()]));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->gate('create');

        $model = $this->newModel();
        $this->fill($model, $this->validateData($request));
        $this->applyCustomFields($model, $request);
        $model->save();
        $this->afterSave($model, $request);

        return $this->redirect("{$this->label()} created.");
    }

    public function edit(int $id): View
    {
        $this->gate('edit');

        return view('rjcms::admin.publishable.edit', $this->viewData(['record' => $this->find($id)]));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->gate('edit');

        $model = $this->find($id);
        $this->fill($model, $this->validateData($request, $model));
        $this->applyCustomFields($model, $request);
        $model->save();
        $this->afterSave($model, $request);

        return $this->redirect("{$this->label()} updated.");
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->gate('delete');

        $this->find($id)->delete();

        return $this->redirect("{$this->label()} deleted.");
    }

    /**
     * Authorize an action only against this type's permission.
     */
    protected function gate(string $action): void
    {
        Gate::authorize("{$action}_{$this->key()}");
    }

    protected function find(int $id): Model
    {
        return $this->baseQuery()->findOrFail($id);
    }

    /**
     * Custom fields defined for this content type.
     *
     * @return Collection<int, CustomField>
     */
    protected function customFields()
    {
        return CustomField::forGroup($this->key());
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateData(Request $request, ?Model $model = null): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique($this->table(), 'slug')->ignore($model?->getKey())],
            'body' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'integer', Rule::exists('medias', 'id')],
            'status' => ['required', Rule::in(['draft', 'published'])],
        ];

        foreach ($this->customFields() as $customField) {
            $field = $customField->asField();
            $rules += $field->handler()->rules($field);
        }

        return $request->validate($rules + $this->extraRules($model));
    }

    /**
     * Extra validation rules contributed by a subclass (e.g. categories).
     *
     * @return array<string, mixed>
     */
    protected function extraRules(?Model $model = null): array
    {
        return [];
    }

    /**
     * Hook for a subclass to persist related data after the model is saved.
     */
    protected function afterSave(Model $model, Request $request): void {}

    /**
     * Extra data merged into every form view (e.g. selectable categories).
     *
     * @return array<string, mixed>
     */
    protected function formExtras(): array
    {
        return [];
    }

    /**
     * Resolve and store each custom field's value into the model's meta column.
     */
    protected function applyCustomFields(Model $model, Request $request): void
    {
        $fields = $this->customFields();

        if ($fields->isEmpty()) {
            return;
        }

        $meta = is_array($model->meta) ? $model->meta : [];

        foreach ($fields as $customField) {
            $field = $customField->asField();
            $meta[$customField->key] = $field->handler()->persistValue($field, $request, $model);
        }

        $model->meta = $meta;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function fill(Model $model, array $data): void
    {
        $model->title = $data['title'];
        $model->slug = $data['slug'];
        $model->body = $data['body'] ?? null;
        $model->featured_image = $data['featured_image'] ?? null;
        $model->status = $data['status'];
    }

    protected function redirect(string $status): RedirectResponse
    {
        return redirect()->route("admin.{$this->key()}.index")->with('status', $status);
    }

    /**
     * Merge the per-type identifiers into the view payload.
     *
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    protected function viewData(array $extra): array
    {
        return [
            'key' => $this->key(),
            'label' => $this->label(),
            'publicRoute' => $this->publicRoute(),
            'statuses' => ['draft' => 'Draft', 'published' => 'Published'],
            'customFields' => $this->customFields(),
            ...$this->formExtras(),
            ...$extra,
        ];
    }
}
