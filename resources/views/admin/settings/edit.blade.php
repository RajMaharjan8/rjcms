@extends('rjcms::layouts.admin')

@section('title', 'Edit setting · ' . config('app.name'))
@section('page-title', 'Edit setting: ' . $setting->display_name)

@section('page-actions')
    @if (! $setting->isLocked())
        @can('delete_settings')
            <form method="POST" action="{{ route('admin.settings.destroy', $setting) }}"
                  onsubmit="return confirm('Delete this setting and its value?')">
                @csrf
                @method('DELETE')
                <x-admin.wp-button type="submit" variant="danger">Delete</x-admin.wp-button>
            </form>
        @endcan
    @endif
@endsection

@section('content')
@include('rjcms::admin.settings._definition-form', [
    'action' => route('admin.settings.update', $setting),
    'method' => 'PUT',
    'submitLabel' => 'Save setting',
    'state' => old('payload') ? json_decode(old('payload'), true) : $state,
])
@endsection
