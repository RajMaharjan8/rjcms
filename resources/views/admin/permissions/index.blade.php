@extends('rjcms::layouts.admin')

@section('title', 'Permissions · ' . config('app.name'))
@section('page-title', 'Permissions')

@section('page-actions')
    @can('create_permissions')
        <x-admin.button :href="route('admin.permissions.create')">New permission</x-admin.button>
    @endcan
@endsection

@section('content')
    <livewire:admin.permissions-manager />
@endsection
