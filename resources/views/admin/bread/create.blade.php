@extends('rjcms::layouts.admin')

@section('title', 'New ' . $bread->name . ' · ' . config('app.name'))
@section('page-title', 'New ' . Str::lower($bread->name))

@section('content')
<form method="POST" action="{{ route('admin.bread.store', $bread) }}"
      enctype="multipart/form-data" x-data="{ saving: false }" @submit="saving = true">
    @csrf
    @include('rjcms::admin.bread._form')
</form>
@endsection
