@extends('rjcms::layouts.admin')

@section('title', 'Edit permission · ' . config('app.name'))
@section('page-title', 'Edit permission')

@section('content')
<div class="max-w-2xl rounded-md border border-wp-border bg-white p-6 shadow-sm">
    <form method="POST" action="{{ route('admin.permissions.update', $permission) }}">
        @csrf
        @method('PUT')
        @include('rjcms::admin.permissions._form', ['permission' => $permission])
    </form>
</div>
@endsection
