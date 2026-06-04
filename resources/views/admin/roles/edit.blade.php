@extends('rjcms::layouts.admin')

@section('title', 'Edit role · ' . config('app.name'))
@section('page-title', 'Edit role')

@section('content')
<div class="max-w-2xl rounded-md border border-wp-border bg-white p-6 shadow-sm">
    <form method="POST" action="{{ route('admin.roles.update', $role) }}">
        @csrf
        @method('PUT')
        @include('rjcms::admin.roles._form', ['permissions' => $permissions, 'role' => $role])
    </form>
</div>
@endsection
