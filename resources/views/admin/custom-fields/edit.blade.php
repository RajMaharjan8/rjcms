@extends('rjcms::layouts.admin')

@section('title', 'Edit ' . $groupLabel . ' field · ' . config('app.name'))
@section('page-title', 'Edit ' . $groupLabel . ' field')

@section('page-actions')
    <form method="POST" action="{{ route('admin.content-fields.destroy', [$group, $field]) }}"
          onsubmit="return confirm('Remove this field? Stored values are kept in existing records.')">
        @csrf
        @method('DELETE')
        <x-admin.wp-button type="submit" variant="danger">Remove</x-admin.wp-button>
    </form>
@endsection

@section('content')
@include('rjcms::admin.custom-fields._form', [
    'action' => route('admin.content-fields.update', [$group, $field]),
    'method' => 'PUT',
    'submitLabel' => 'Save field',
])
@endsection
