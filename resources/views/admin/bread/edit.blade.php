@extends('rjcms::layouts.admin')

@section('title', 'Edit ' . $bread->name . ' · ' . config('app.name'))
@section('page-title', 'Edit ' . Str::lower($bread->name))

@section('content')
<form method="POST" action="{{ route('admin.bread.update', [$bread, $record->getKey()]) }}"
      enctype="multipart/form-data" x-data="{ saving: false }" @submit="saving = true">
    @csrf
    @method('PUT')
    @include('rjcms::admin.bread._form')
</form>
@endsection
