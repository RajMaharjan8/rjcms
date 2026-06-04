@extends('rjcms::layouts.admin')

@section('title', 'Edit category · ' . config('app.name'))
@section('page-title', 'Edit category: ' . $category->name)

@section('content')
<div class="max-w-2xl rounded-md border border-wp-border bg-white p-6 shadow-sm">
    <form method="POST" action="{{ route('admin.categories.update', $category) }}">
        @csrf
        @method('PUT')
        @include('rjcms::admin.categories._form')
    </form>
</div>
@endsection
