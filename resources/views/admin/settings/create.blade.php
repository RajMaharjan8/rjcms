@extends('rjcms::layouts.admin')

@section('title', 'New setting · ' . config('app.name'))
@section('page-title', 'New setting')

@section('content')
@include('rjcms::admin.settings._definition-form', [
    'action' => route('admin.settings.store'),
    'method' => 'POST',
    'submitLabel' => 'Create setting',
    'state' => [
        'key' => '',
        'display_name' => '',
        'group' => old('group', 'General'),
        'type' => 'text',
        'required' => false,
        'order' => 0,
        'choices' => [],
        'subfields' => [],
    ],
])
@endsection
