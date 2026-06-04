@extends('rjcms::layouts.admin')

@section('title', 'New category · ' . config('app.name'))
@section('page-title', 'New category')

@section('content')
<div class="max-w-2xl rounded-md border border-wp-border bg-white p-6 shadow-sm">
    <form method="POST" action="{{ route('admin.categories.store') }}">
        @csrf
        @include('rjcms::admin.categories._form')
    </form>
</div>
@endsection
