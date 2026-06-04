<?php

namespace Rjcodes\Rjcms\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Rjcodes\Rjcms\Http\Controllers\Controller;
use Rjcodes\Rjcms\Http\Requests\Admin\MenuRequest;
use Rjcodes\Rjcms\Models\Menu;
use Rjcodes\Rjcms\Models\MenuItem;

/**
 * Admin CRUD plus a visual builder for navigation menus and their items.
 */
class MenuController extends Controller implements HasMiddleware
{
    /**
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('can:browse_menus', only: ['index']),
            new Middleware('can:create_menus', only: ['create', 'store']),
            new Middleware('can:edit_menus', only: ['edit', 'update']),
            new Middleware('can:delete_menus', only: ['destroy']),
        ];
    }

    /**
     * List every menu.
     */
    public function index(): View
    {
        return view('rjcms::admin.menus.index', [
            'menus' => Menu::withCount('items')->orderBy('name')->get(),
        ]);
    }

    /**
     * Show the form to register a new menu.
     */
    public function create(): View
    {
        return view('rjcms::admin.menus.create', ['menu' => new Menu]);
    }

    /**
     * Store a new menu, then open its item builder.
     */
    public function store(MenuRequest $request): RedirectResponse
    {
        $menu = Menu::create($request->validated());

        return redirect()
            ->route('admin.menus.edit', $menu)
            ->with('status', 'Menu created — now add some links.');
    }

    /**
     * Open the item builder for a menu.
     */
    public function edit(Menu $menu): View
    {
        return view('rjcms::admin.menus.edit', [
            'menu' => $menu,
            'state' => $this->menuState($menu),
        ]);
    }

    /**
     * Persist the menu's name/slug and rebuild its item tree from the payload.
     */
    public function update(Request $request, Menu $menu): RedirectResponse
    {
        $payload = json_decode((string) $request->input('payload'), true);
        $data = is_array($payload) ? $payload : [];

        if (blank($data['slug'] ?? null) && filled($data['name'] ?? null)) {
            $data['slug'] = str($data['name'])->slug()->value();
        }

        Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('menus', 'slug')->ignore($menu)],
            'items' => ['array'],
            'items.*.title' => ['required', 'string', 'max:255'],
            'items.*.url' => ['nullable', 'string', 'max:2000'],
            'items.*.target' => ['nullable', Rule::in(['_self', '_blank'])],
        ], attributes: [
            'items.*.title' => 'link title',
            'items.*.url' => 'link URL',
        ])->validate();

        $menu->update(['name' => $data['name'], 'slug' => $data['slug']]);

        $this->rebuildItems($menu, array_values($data['items'] ?? []));

        return redirect()
            ->route('admin.menus.index')
            ->with('status', "Menu \"{$menu->name}\" saved.");
    }

    /**
     * Delete a menu and its items.
     */
    public function destroy(Menu $menu): RedirectResponse
    {
        $menu->delete();

        return redirect()->route('admin.menus.index')->with('status', 'Menu deleted.');
    }

    /**
     * Replace all of a menu's items with the submitted set, resolving each
     * item's client-side parent uid to the real id it was just created with.
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    private function rebuildItems(Menu $menu, array $items): void
    {
        // Clear parents first so the self-referencing rows delete cleanly.
        $menu->items()->update(['parent_id' => null]);
        $menu->items()->delete();

        /** @var array<string, MenuItem> $created */
        $created = [];

        foreach ($items as $order => $item) {
            $created[$item['uid']] = $menu->items()->create([
                'title' => $item['title'],
                'url' => filled($item['url'] ?? null) ? $item['url'] : null,
                'target' => ($item['target'] ?? null) === '_blank' ? '_blank' : '_self',
                'order' => $order,
            ]);
        }

        foreach ($items as $item) {
            $parentUid = $item['parent'] ?? null;

            if (filled($parentUid) && $parentUid !== $item['uid'] && isset($created[$parentUid])) {
                $created[$item['uid']]->update(['parent_id' => $created[$parentUid]->id]);
            }
        }
    }

    /**
     * The editable client-side state for a menu and its items.
     *
     * @return array<string, mixed>
     */
    private function menuState(Menu $menu): array
    {
        return [
            'name' => $menu->name,
            'slug' => $menu->slug,
            'items' => $menu->items->map(fn (MenuItem $item): array => [
                'uid' => 'i'.$item->id,
                'title' => $item->title,
                'url' => (string) $item->url,
                'target' => $item->target,
                'parent' => $item->parent_id ? 'i'.$item->parent_id : '',
            ])->all(),
        ];
    }
}
