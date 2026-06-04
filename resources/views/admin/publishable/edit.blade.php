@extends('rjcms::layouts.admin')

@section('title', 'Edit ' . $label . ' · ' . config('app.name'))
@section('page-title', 'Edit ' . $label . ': ' . $record->title)

@section('page-actions')
    @if ($record->status === 'published')
        <x-admin.wp-button variant="secondary" :href="route($publicRoute, $record->slug)" target="_blank" rel="noopener">View on site</x-admin.wp-button>
    @endif
@endsection

@section('content')
@include('rjcms::admin.publishable._form', ['action' => route('admin.'.$key.'.update', $record->getKey())])
@endsection
