@extends('rjcms::layouts.admin')

@section('title', 'Media · ' . config('app.name'))
@section('page-title', 'Media library')

@section('page-actions')
    @can('create_medias')
        <x-admin.button :href="route('admin.media.create')">Upload</x-admin.button>
    @endcan
@endsection

@section('content')
    <livewire:admin.media-library />
@endsection
