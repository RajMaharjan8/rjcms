@extends('rjcms::layouts.admin')

@section('title', 'New ' . $groupLabel . ' field · ' . config('app.name'))
@section('page-title', 'New ' . $groupLabel . ' field')

@section('content')
@include('rjcms::admin.custom-fields._form', [
    'action' => route('admin.content-fields.store', $group),
    'method' => 'POST',
    'submitLabel' => 'Add field',
])
@endsection
