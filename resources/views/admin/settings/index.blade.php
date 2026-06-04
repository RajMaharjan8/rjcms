@extends('rjcms::layouts.admin')

@section('title', 'Settings · ' . config('app.name'))
@section('page-title', 'Settings')

@section('page-actions')
    @can('create_settings')
        <x-admin.wp-button :href="route('admin.settings.create')">+ New Setting</x-admin.wp-button>
    @endcan
@endsection

@section('content')
@if ($errors->any())
    <div class="mb-6 rounded border-l-4 border-red-500 bg-red-50 px-4 py-3 text-sm text-red-700">
        <p class="font-semibold">Please fix the following:</p>
        <ul class="mt-1 list-inside list-disc">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if ($groups->isEmpty())
    <div class="rounded-md border border-dashed border-wp-border bg-white px-4 py-12 text-center shadow-sm">
        <p class="text-sm font-medium text-wp-ink">No settings yet</p>
        <p class="mt-1 text-xs text-wp-muted">Create your first setting and assign it to a category.</p>
        @can('create_settings')
            <x-admin.wp-button :href="route('admin.settings.create')" size="sm" class="mt-3">+ New Setting</x-admin.wp-button>
        @endcan
    </div>
@else
    <div class="mb-5 rounded border-l-4 border-wp-blue bg-white px-4 py-3 text-sm text-wp-ink shadow-sm">
        Read any setting in Blade or PHP with <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs text-wp-ink">setting('key')</code>,
        e.g. <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs text-wp-ink">@verbatim{{ setting('site.name') }}@endverbatim</code>.
        Pass a second argument as a fallback: <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs text-wp-ink">setting('site.name', 'My Site')</code>.
    </div>

    <div x-data="{ tab: @js($groups->keys()->first()) }" class="flex flex-col gap-5">
        {{-- Category tabs --}}
        <nav class="flex flex-wrap gap-1 border-b border-wp-border">
            @foreach ($groups as $group => $items)
                <button type="button" @click="tab = @js($group)"
                        class="-mb-px border-b-2 px-4 py-2 text-sm font-medium transition"
                        :class="tab === @js($group) ? 'border-wp-blue text-wp-blue' : 'border-transparent text-wp-muted hover:text-wp-ink'">
                    {{ $group }}
                </button>
            @endforeach
        </nav>

        <form method="POST" action="{{ route('admin.settings.save') }}" class="flex flex-col gap-5">
            @csrf
            @method('PUT')

            @foreach ($groups as $group => $items)
                <div x-show="tab === @js($group)" x-cloak>
                    <x-admin.postbox :title="$group">
                        <div class="flex flex-col divide-y divide-wp-border-light">
                            @foreach ($items as $setting)
                                <div class="flex items-start justify-between gap-4 py-4 first:pt-0 last:pb-0">
                                    <div class="min-w-0 flex-1">
                                        @include($setting->fieldType()->formView(), [
                                            'field' => $setting->asField(),
                                            'value' => $setting->typedValue(),
                                        ])
                                        <p class="mt-1 font-mono text-xs text-wp-muted" title="Use this in your views">setting('{{ $setting->key }}')</p>
                                    </div>
                                    @can('edit_settings')
                                        <a href="{{ route('admin.settings.edit', $setting) }}"
                                           class="shrink-0 text-sm font-medium text-wp-blue hover:text-wp-blue-hover">Edit</a>
                                    @endcan
                                </div>
                            @endforeach
                        </div>
                    </x-admin.postbox>
                </div>
            @endforeach

            @can('edit_settings')
                <div>
                    <x-admin.wp-button type="submit">Save Settings</x-admin.wp-button>
                </div>
            @endcan
        </form>
    </div>
@endif
@endsection
