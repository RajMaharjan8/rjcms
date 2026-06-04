@extends('rjcms::layouts.admin')

@section('title', 'New role · ' . config('app.name'))
@section('page-title', 'New role')

@section('content')
<div class="max-w-2xl rounded-md border border-wp-border bg-white p-6 shadow-sm">
    <form method="POST" action="{{ route('admin.roles.store') }}">
        @csrf
        @include('rjcms::admin.roles._form', ['permissions' => $permissions, 'role' => null])
    </form>
</div>
@endsection
