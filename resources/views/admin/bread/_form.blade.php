@php($context = $record->exists ? 'edit' : 'add')
@php($fields = $bread->fieldsFor($context))
@php($ungrouped = $fields->filter(fn ($field) => blank($field->group))->values())
@php($groups = $fields->filter(fn ($field) => filled($field->group))->groupBy('group'))

<div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
    {{-- Main column: primary (ungrouped) fields --}}
    <div class="flex flex-col gap-6">
        @if ($errors->any())
            <div class="rounded border-l-4 border-red-500 bg-red-50 px-4 py-3 text-sm text-red-700">
                <p class="font-semibold">Please fix the following:</p>
                <ul class="mt-1 list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <x-admin.postbox :title="$bread->name">
            @if ($ungrouped->isNotEmpty())
                <div class="flex flex-col gap-5">
                    @foreach ($ungrouped as $field)
                        @include($field->type->formView(), [
                            'field' => $field,
                            'value' => $field->handler()->formValue($field, $record),
                        ])
                    @endforeach
                </div>
            @else
                <p class="text-sm text-wp-muted">All fields are organised into the panels on the right.</p>
            @endif
        </x-admin.postbox>
    </div>

    {{-- Sidebar: save panel + grouped field panels --}}
    <div class="flex flex-col gap-6 lg:sticky lg:top-6">
        <x-admin.postbox title="Save">
            <dl class="space-y-2.5 text-xs">
                <div class="flex items-center justify-between">
                    <dt class="font-medium text-wp-muted">Status</dt>
                    <dd>
                        @if ($record->exists)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-2 py-0.5 font-medium text-green-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>Saved
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2 py-0.5 font-medium text-gray-600">
                                <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>Draft
                            </span>
                        @endif
                    </dd>
                </div>
                @if ($record->exists && $record->updated_at)
                    <div class="flex items-center justify-between">
                        <dt class="font-medium text-wp-muted">Last updated</dt>
                        <dd class="text-wp-ink" title="{{ $record->updated_at->toDayDateTimeString() }}">{{ $record->updated_at->diffForHumans() }}</dd>
                    </div>
                @endif
            </dl>

            <x-slot:footer>
                <div class="flex items-center justify-between gap-3">
                    <a href="{{ route('admin.bread.index', $bread) }}"
                       class="text-sm font-medium text-wp-muted hover:text-wp-ink">Cancel</a>
                    <x-admin.button type="submit" x-bind:disabled="saving"
                                    class="disabled:cursor-not-allowed disabled:opacity-60"
                                    x-text="saving ? 'Saving…' : 'Save'">Save</x-admin.button>
                </div>
            </x-slot:footer>
        </x-admin.postbox>

        @foreach ($groups as $groupName => $groupFields)
            <x-admin.postbox :title="$groupName">
                <div class="flex flex-col gap-5">
                    @foreach ($groupFields as $field)
                        @include($field->type->formView(), [
                            'field' => $field,
                            'value' => $field->handler()->formValue($field, $record),
                        ])
                    @endforeach
                </div>
            </x-admin.postbox>
        @endforeach
    </div>
</div>
