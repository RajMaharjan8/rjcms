@extends('rjcms::layouts.admin')

@section('title', 'New ' . $label . ' · ' . config('app.name'))
@section('page-title', 'New ' . $label)

@section('content')
@include('rjcms::admin.publishable._form', ['action' => route('admin.'.$key.'.store')])
@endsection
