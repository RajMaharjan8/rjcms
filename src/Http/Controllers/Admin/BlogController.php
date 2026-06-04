<?php

namespace Rjcodes\Rjcms\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Rjcodes\Rjcms\Models\Category;
use Rjcodes\Rjcms\Models\Post;
use Rjcodes\Rjcms\Models\Setting;

/**
 * Admin CRUD for the built-in Blogs content type (backed by the Post model).
 */
class BlogController extends PublishableController
{
    protected function key(): string
    {
        return 'blogs';
    }

    protected function label(): string
    {
        return 'Blog';
    }

    protected function table(): string
    {
        return 'posts';
    }

    protected function newModel(): Model
    {
        return new Post;
    }

    protected function baseQuery(): Builder
    {
        return Post::query();
    }

    protected function publicRoute(): string
    {
        return 'blog.show';
    }

    /**
     * The setting key holding the Blade view used to render every blog post.
     */
    private const TEMPLATE_SETTING = 'blog.post_template';

    /**
     * Posts may be filed under any number of existing categories.
     *
     * @return array<string, mixed>
     */
    protected function extraRules(?Model $model = null): array
    {
        return [
            'categories' => ['array'],
            'categories.*' => [Rule::exists('categories', 'id')],
        ];
    }

    /**
     * Sync the selected categories onto the post.
     */
    protected function afterSave(Model $model, Request $request): void
    {
        $model->categories()->sync($request->input('categories', []));
    }

    /**
     * Provide the selectable categories to the post form.
     *
     * @return array<string, mixed>
     */
    protected function formExtras(): array
    {
        return ['categoryOptions' => Category::orderBy('name')->get()];
    }

    /**
     * Show the form for the site-wide blog post template (applies to all posts).
     */
    public function editTemplate(): View
    {
        Gate::authorize('edit_blogs');

        return view('rjcms::admin.blogs.template', ['template' => setting(self::TEMPLATE_SETTING)]);
    }

    /**
     * Save the site-wide blog post template.
     */
    public function updateTemplate(Request $request): RedirectResponse
    {
        Gate::authorize('edit_blogs');

        $data = $request->validate([
            'template' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9_.\-]+$/'],
        ]);

        $setting = Setting::firstOrNew(['key' => self::TEMPLATE_SETTING]);

        if (! $setting->exists) {
            $setting->fill(['group' => 'Blog', 'display_name' => 'Default post template', 'type' => 'text', 'order' => 1]);
        }

        $setting->storeValue($data['template'] ?? null);

        return redirect()->route('admin.blogs.index')->with('status', 'Blog template updated.');
    }
}
