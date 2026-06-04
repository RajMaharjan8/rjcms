<?php

namespace Rjcodes\Rjcms\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Rjcodes\Rjcms\Http\Controllers\Controller;
use Rjcodes\Rjcms\Http\Requests\Admin\CategoryRequest;
use Rjcodes\Rjcms\Models\Category;

/**
 * Admin CRUD for blog categories.
 */
class CategoryController extends Controller implements HasMiddleware
{
    /**
     * Permission gates applied per action.
     *
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('can:browse_categories', only: ['index']),
            new Middleware('can:create_categories', only: ['create', 'store']),
            new Middleware('can:edit_categories', only: ['edit', 'update']),
            new Middleware('can:delete_categories', only: ['destroy']),
        ];
    }

    /**
     * Browse all categories.
     */
    public function index(): View
    {
        return view('rjcms::admin.categories.index', [
            'categories' => Category::withCount('posts')->orderBy('name')->paginate(15),
        ]);
    }

    /**
     * Show the form to add a category.
     */
    public function create(): View
    {
        return view('rjcms::admin.categories.create', ['category' => new Category]);
    }

    /**
     * Store a new category.
     */
    public function store(CategoryRequest $request): RedirectResponse
    {
        Category::create($request->validated());

        return redirect()->route('admin.categories.index')->with('status', 'Category created.');
    }

    /**
     * Show the form to edit a category.
     */
    public function edit(Category $category): View
    {
        return view('rjcms::admin.categories.edit', ['category' => $category]);
    }

    /**
     * Update an existing category.
     */
    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()->route('admin.categories.index')->with('status', 'Category updated.');
    }

    /**
     * Delete a category (its post associations drop via the pivot's cascade).
     */
    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return redirect()->route('admin.categories.index')->with('status', 'Category deleted.');
    }
}
