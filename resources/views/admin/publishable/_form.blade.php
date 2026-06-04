@php($inputClass = 'w-full rounded border border-wp-border bg-white px-3 py-1.5 text-sm text-wp-ink shadow-sm transition focus:border-wp-blue focus:ring-1 focus:ring-wp-blue focus:outline-none')

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

<form method="POST" action="{{ $action }}"
      x-data="{ slug: @js(old('slug', $record->slug)), slugTouched: {{ $record->exists ? 'true' : 'false' }} }"
      class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_300px]">
    @csrf
    @if ($record->exists)
        @method('PUT')
    @endif

    {{-- Main column --}}
    <div class="flex flex-col gap-6">
        <x-admin.postbox title="Content" body-class="px-5 py-1">
            <div class="divide-y divide-wp-border-light">
                <div class="flex flex-col gap-1.5 py-4">
                    <label for="title" class="text-sm font-semibold text-wp-ink">Title</label>
                    <input id="title" name="title" type="text" value="{{ old('title', $record->title) }}" required
                           @input="if (! slugTouched) slug = $event.target.value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '')"
                           class="{{ $inputClass }}">
                    @error('title') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex flex-col gap-1.5 py-4">
                    <label for="slug" class="text-sm font-semibold text-wp-ink">Slug</label>
                    <input id="slug" name="slug" type="text" x-model="slug" @input="slugTouched = true" required class="{{ $inputClass }}">
                    <p class="text-xs text-wp-muted">URL: <span class="font-mono">/{{ $key === 'blogs' ? 'blog/' : '' }}<span x-text="slug || 'slug'"></span></span></p>
                    @error('slug') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex flex-col gap-1.5 py-4">
                    <label for="body" class="text-sm font-semibold text-wp-ink">Body</label>
                    <textarea id="body" name="body" rows="12" data-rich-editor class="{{ $inputClass }}">{{ old('body', $record->body) }}</textarea>
                    @error('body') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </x-admin.postbox>

        @if ($customFields->isNotEmpty())
            <x-admin.postbox title="Custom Fields">
                <div class="flex flex-col gap-5">
                    @foreach ($customFields as $customField)
                        @php($cfField = $customField->asField())
                        @include($cfField->type->formView(), ['field' => $cfField, 'value' => $record->field($customField->key)])
                    @endforeach
                </div>
            </x-admin.postbox>
        @endif
    </div>

    @include('rjcms::admin.partials._rich-editor')

    {{-- Sidebar --}}
    <div class="flex flex-col gap-6 lg:sticky lg:top-6">
        <x-admin.postbox title="Publish">
            <div class="flex flex-col gap-1.5">
                <label for="status" class="text-sm font-semibold text-wp-ink">Status</label>
                <select id="status" name="status" class="{{ $inputClass }}">
                    @foreach ($statuses as $value => $stateLabel)
                        <option value="{{ $value }}" @selected(old('status', $record->status ?? 'draft') === $value)>{{ $stateLabel }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-wp-muted">Drafts are hidden from the public site.</p>
            </div>

            <x-slot:footer>
                <div class="flex items-center justify-between gap-3">
                    <a href="{{ route('admin.'.$key.'.index') }}" class="text-sm font-medium text-wp-muted hover:text-wp-ink">Cancel</a>
                    <x-admin.wp-button type="submit">{{ $record->exists ? 'Save' : 'Publish' }}</x-admin.wp-button>
                </div>
            </x-slot:footer>
        </x-admin.postbox>

        @isset($categoryOptions)
            @php($selectedCategories = old('categories', $record->exists ? $record->categories->pluck('id')->all() : []))
            <x-admin.postbox title="Categories">
                @forelse ($categoryOptions as $category)
                    <label class="flex items-center gap-2 py-1 text-sm text-wp-ink">
                        <input type="checkbox" name="categories[]" value="{{ $category->id }}"
                               @checked(in_array($category->id, $selectedCategories))
                               class="rounded border-wp-border text-wp-blue focus:ring-wp-blue">
                        {{ $category->name }}
                    </label>
                @empty
                    <p class="text-xs text-wp-muted">
                        No categories yet. <a href="{{ route('admin.categories.create') }}" class="text-wp-blue hover:underline">Add one</a>.
                    </p>
                @endforelse
            </x-admin.postbox>
        @endisset

        <x-admin.postbox title="Featured Image">
            <livewire:admin.media-picker name="featured_image" :value="old('featured_image', $record->featured_image)"
                                         :key="$key.'-featured-'.($record->getKey() ?? 'new')" />
        </x-admin.postbox>
    </div>
</form>
