@extends('rjcms::layouts.admin')

@section('title', 'Edit user · ' . config('app.name'))
@section('page-title', 'Edit user')

@section('content')
<div class="max-w-2xl rounded-md border border-wp-border bg-white p-6 shadow-sm">
    <form method="POST" action="{{ route('admin.users.update', $user) }}">
        @csrf
        @method('PUT')
        @include('rjcms::admin.users._form', ['roles' => $roles, 'user' => $user])
    </form>
</div>
@endsection
