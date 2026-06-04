@php($inputClass = 'w-full rounded border border-wp-border bg-white px-3 py-2 text-sm text-wp-ink shadow-sm transition focus:border-wp-blue focus:ring-1 focus:ring-wp-blue focus:outline-none')

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

<div class="flex flex-col gap-5"
     x-data="{ slug: @js(old('slug', $category->slug)), slugTouched: {{ $category->exists ? 'true' : 'false' }} }">
    <x-admin.field label="Name" name="name">
        <input id="name" name="name" type="text" value="{{ old('name', $category->name) }}" required
               @input="if (! slugTouched) slug = $event.target.value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '')"
               class="{{ $inputClass }}">
    </x-admin.field>

    <x-admin.field label="Slug" name="slug" hint="Used in the category URL, e.g. /blog/category/news">
        <input id="slug" name="slug" type="text" x-model="slug" @input="slugTouched = true" required class="{{ $inputClass }}">
    </x-admin.field>

    <x-admin.field label="Description" name="description">
        <textarea id="description" name="description" rows="3" class="{{ $inputClass }}">{{ old('description', $category->description) }}</textarea>
    </x-admin.field>

    <div class="flex gap-3 pt-2">
        <x-admin.button type="submit">Save</x-admin.button>
        <x-admin.button variant="secondary" :href="route('admin.categories.index')">Cancel</x-admin.button>
    </div>
</div>
