@extends('rjcms::layouts.admin')

@section('title', 'New permission · ' . config('app.name'))
@section('page-title', 'New permission')

@section('content')
<div class="max-w-2xl rounded-md border border-wp-border bg-white p-6 shadow-sm">
    <form method="POST" action="{{ route('admin.permissions.store') }}">
        @csrf
        @include('rjcms::admin.permissions._form', ['permission' => null])
    </form>
</div>
@endsection
