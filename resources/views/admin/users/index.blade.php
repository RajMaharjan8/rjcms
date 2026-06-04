@extends('rjcms::layouts.admin')

@section('title', 'Users · ' . config('app.name'))
@section('page-title', 'Users')

@section('page-actions')
    @can('create_users')
        <x-admin.button :href="route('admin.users.create')">New user</x-admin.button>
    @endcan
@endsection

@section('content')
    <livewire:admin.users-table />
@endsection
