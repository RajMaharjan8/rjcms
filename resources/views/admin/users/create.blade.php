@extends('rjcms::layouts.admin')

@section('title', 'New user · ' . config('app.name'))
@section('page-title', 'New user')

@section('content')
<div class="max-w-2xl rounded-md border border-wp-border bg-white p-6 shadow-sm">
    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf
        @include('rjcms::admin.users._form', ['roles' => $roles, 'user' => null])
    </form>
</div>
@endsection
