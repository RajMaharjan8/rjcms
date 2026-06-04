@extends('rjcms::layouts.app')

@section('body')
<div x-data="{ sidebarOpen: false }" class="min-h-screen bg-wp-bg">
    {{-- Mobile overlay --}}
    <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"
         class="fixed inset-0 z-30 bg-gray-900/50 lg:hidden" style="display: none;"></div>

    {{-- Sidebar (WordPress-style dark admin menu) --}}
    <aside x-bind:class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-40 flex w-60 flex-col bg-wp-menu transition-transform lg:translate-x-0">
        <div class="flex h-14 items-center gap-2 bg-black/20 px-5">
            <span class="text-base font-semibold text-white">{{ config('app.name') }}</span>
            <span class="rounded bg-wp-blue px-1.5 py-0.5 text-[10px] font-semibold tracking-wide text-white uppercase">Admin</span>
        </div>

        <nav class="flex-1 overflow-y-auto py-2 text-sm">
            @php
                $navItems = [
                    ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'pattern' => 'admin.dashboard', 'permission' => null],
                    ['label' => 'Blogs', 'route' => 'admin.blogs.index', 'pattern' => 'admin.blogs.*', 'permission' => 'browse_blogs'],
                    ['label' => 'Categories', 'route' => 'admin.categories.index', 'pattern' => 'admin.categories.*', 'permission' => 'browse_categories'],
                    ['label' => 'Users', 'route' => 'admin.users.index', 'pattern' => 'admin.users.*', 'permission' => 'browse_users'],
                    ['label' => 'Roles', 'route' => 'admin.roles.index', 'pattern' => 'admin.roles.*', 'permission' => 'browse_roles'],
                    ['label' => 'Permissions', 'route' => 'admin.permissions.index', 'pattern' => 'admin.permissions.*', 'permission' => 'browse_permissions'],
                    ['label' => 'Media', 'route' => 'admin.media.index', 'pattern' => 'admin.media.*', 'permission' => 'browse_medias'],
                    ['label' => 'Menus', 'route' => 'admin.menus.index', 'pattern' => 'admin.menus.*', 'permission' => 'browse_menus'],
                    ['label' => 'BREAD builder', 'route' => 'admin.breads.index', 'pattern' => 'admin.breads.*', 'permission' => 'browse_breads'],
                    ['label' => 'Settings', 'route' => 'admin.settings.index', 'pattern' => 'admin.settings.*', 'permission' => 'browse_settings'],
                ];
            @endphp
            @foreach ($navItems as $item)
                @if (! $item['permission'] || auth()->user()->can($item['permission']))
                    @php($active = request()->routeIs($item['pattern']))
                    <a href="{{ route($item['route']) }}"
                       @class([
                           'flex items-center border-l-4 px-4 py-2.5 font-medium transition',
                           'border-white bg-wp-blue text-white' => $active,
                           'border-transparent text-wp-menu-text hover:bg-wp-menu-hover hover:text-white' => ! $active,
                       ])>
                        {{ $item['label'] }}
                    </a>
                @endif
            @endforeach

            @if (filled($breads ?? null) && $breads->isNotEmpty())
                <p class="px-4 pt-5 pb-1 text-[11px] font-semibold tracking-wider text-wp-menu-text/60 uppercase">Content</p>
                @foreach ($breads as $bread)
                    @php($active = request()->routeIs('admin.bread.*') && request()->route('bread')?->is($bread))
                    <a href="{{ route('admin.bread.index', $bread) }}"
                       @class([
                           'flex items-center border-l-4 px-4 py-2.5 font-medium transition',
                           'border-white bg-wp-blue text-white' => $active,
                           'border-transparent text-wp-menu-text hover:bg-wp-menu-hover hover:text-white' => ! $active,
                       ])>
                        {{ $bread->name_plural }}
                    </a>
                @endforeach
            @endif
        </nav>

        <div class="border-t border-white/10 p-4">
            <a href="{{ route('admin.account.edit') }}" class="flex items-center gap-2.5 rounded px-1 py-0.5 transition hover:bg-wp-menu-hover">
                <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full bg-wp-menu-hover text-sm font-semibold text-white"
                      style="width:2.25rem;height:2.25rem;flex:0 0 auto;overflow:hidden">
                    @if (auth()->user()->avatarUrl())
                        <img src="{{ auth()->user()->avatarUrl() }}" alt="{{ auth()->user()->name }}" width="36" height="36"
                             class="h-full w-full object-cover" style="width:100%;height:100%;aspect-ratio:1/1;object-fit:cover;display:block">
                    @else
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    @endif
                </span>
                <span class="min-w-0">
                    <span class="block text-sm font-medium text-white">{{ auth()->user()->name }}</span>
                    <span class="block truncate text-xs text-wp-menu-text">{{ auth()->user()->email }}</span>
                </span>
            </a>
            <div class="mt-3 flex gap-2">
                <a href="{{ route('admin.account.edit') }}"
                   @class([
                       'flex-1 rounded border border-white/20 px-3 py-1.5 text-center text-sm font-medium transition hover:bg-wp-menu-hover hover:text-white',
                       'bg-wp-menu-hover text-white' => request()->routeIs('admin.account.*'),
                       'text-wp-menu-text' => ! request()->routeIs('admin.account.*'),
                   ])>
                    Account
                </a>
                <form method="POST" action="{{ route('admin.logout') }}" class="flex-1">
                    @csrf
                    <button type="submit"
                            class="w-full rounded border border-white/20 px-3 py-1.5 text-sm font-medium text-wp-menu-text transition hover:bg-wp-menu-hover hover:text-white">
                        Log out
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Main column --}}
    <div class="lg:pl-60">
        <header class="flex h-14 items-center gap-3 border-b border-wp-border bg-white px-4 sm:px-6">
            <button type="button" @click="sidebarOpen = true"
                    class="rounded p-1.5 text-wp-muted hover:bg-gray-100 lg:hidden" aria-label="Open sidebar">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                </svg>
            </button>
            <h1 class="text-base font-semibold text-wp-ink">@yield('page-title', 'Admin')</h1>
            <div class="ml-auto">@yield('page-actions')</div>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
            <x-admin.flash />
            @yield('content')
        </main>
    </div>
</div>
@endsection
