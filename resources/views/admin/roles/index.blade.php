@extends('rjcms::layouts.admin')

@section('title', 'Roles · ' . config('app.name'))
@section('page-title', 'Roles')

@section('page-actions')
    @can('create_roles')
        <x-admin.button :href="route('admin.roles.create')">New role</x-admin.button>
    @endcan
@endsection

@section('content')
    <livewire:admin.roles-table />
@endsection
