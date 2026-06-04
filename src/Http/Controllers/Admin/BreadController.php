<?php

namespace Rjcodes\Rjcms\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Rjcodes\Rjcms\Http\Controllers\Controller;
use Rjcodes\Rjcms\Models\Bread;
use Rjcodes\Rjcms\Models\BreadField;

/**
 * Renders Browse / Read / Edit / Add / Delete for any configured BREAD.
 */
class BreadController extends Controller
{
    /**
     * Browse the records of a BREAD.
     */
    public function index(Request $request, Bread $bread): View
    {
        $this->authorizeAction($bread, 'browse');

        $record = $bread->newRecord();
        $perPage = max(1, min($request->integer('perPage', 15), 100));

        return view('rjcms::admin.bread.index', [
            'bread' => $bread,
            'records' => $record->newQuery()
                ->orderByDesc($record->getKeyName())
                ->paginate($perPage)
                ->withQueryString(),
        ]);
    }

    /**
     * Show the form to add a record.
     */
    public function create(Bread $bread): View
    {
        $this->authorizeAction($bread, 'create');

        return view('rjcms::admin.bread.create', [
            'bread' => $bread,
            'record' => $bread->newRecord(),
        ]);
    }

    /**
     * Store a new record.
     */
    public function store(Request $request, Bread $bread): RedirectResponse
    {
        $this->authorizeAction($bread, 'create');

        $record = $bread->newRecord();
        $fields = $this->persist($request, $bread, $record);
        $record->save();
        $this->runAfterSave($fields, $request, $record);

        return $this->redirectToIndex($bread, "{$bread->name} created.");
    }

    /**
     * Read a single record.
     */
    public function show(Bread $bread, int $record): View
    {
        $this->authorizeAction($bread, 'view');

        return view('rjcms::admin.bread.show', [
            'bread' => $bread,
            'record' => $this->findRecord($bread, $record),
        ]);
    }

    /**
     * Show the form to edit a record.
     */
    public function edit(Bread $bread, int $record): View
    {
        $this->authorizeAction($bread, 'edit');

        return view('rjcms::admin.bread.edit', [
            'bread' => $bread,
            'record' => $this->findRecord($bread, $record),
        ]);
    }

    /**
     * Update an existing record.
     */
    public function update(Request $request, Bread $bread, int $record): RedirectResponse
    {
        $this->authorizeAction($bread, 'edit');

        $model = $this->findRecord($bread, $record);
        $fields = $this->persist($request, $bread, $model);
        $model->save();
        $this->runAfterSave($fields, $request, $model);

        return $this->redirectToIndex($bread, "{$bread->name} updated.");
    }

    /**
     * Delete a record.
     */
    public function destroy(Bread $bread, int $record): RedirectResponse
    {
        $this->authorizeAction($bread, 'delete');

        $this->findRecord($bread, $record)->delete();

        return $this->redirectToIndex($bread, "{$bread->name} deleted.");
    }

    /**
     * Authorize a BREAD action, but only when the BREAD is permission-guarded.
     * BREADs created without permissions are open to any authenticated admin.
     */
    private function authorizeAction(Bread $bread, string $action): void
    {
        if ($bread->isPermissioned()) {
            Gate::authorize($bread->permission($action));
        }
    }

    /**
     * Fetch a single record of the BREAD's model, or fail with a 404.
     */
    private function findRecord(Bread $bread, int $id): Model
    {
        return $bread->newRecord()->newQuery()->findOrFail($id);
    }

    /**
     * Validate the request against the BREAD's fields and apply each
     * field's resolved value to the record.
     */
    private function persist(Request $request, Bread $bread, Model $record): Collection
    {
        $fields = $bread->fieldsFor($record->exists ? 'edit' : 'add');

        $rules = [];
        foreach ($fields as $field) {
            $rules += $field->handler()->rules($field);
        }
        $request->validate($rules);

        foreach ($fields as $field) {
            $handler = $field->handler();

            // Pivot / has-many relationship fields persist in runAfterSave().
            if ($handler->writesColumn($field)) {
                $record->{$field->column_name} = $handler->persistValue($field, $request, $record);
            }
        }

        return $fields;
    }

    /**
     * Run each field's post-save hook (used by fields that attach related
     * data, like media-library collections, once the record has an id).
     *
     * @param  Collection<int, BreadField>  $fields
     */
    private function runAfterSave(Collection $fields, Request $request, Model $record): void
    {
        foreach ($fields as $field) {
            $field->handler()->afterSave($field, $request, $record);
        }
    }

    /**
     * Redirect back to a BREAD's browse view with a status message.
     */
    private function redirectToIndex(Bread $bread, string $status): RedirectResponse
    {
        return redirect()->route('admin.bread.index', $bread)->with('status', $status);
    }
}
