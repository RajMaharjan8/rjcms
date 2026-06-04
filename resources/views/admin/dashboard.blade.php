@extends('rjcms::layouts.admin')

@section('title', 'Dashboard · ' . config('app.name'))
@section('page-title', 'Dashboard')

@section('content')
<p class="text-sm text-wp-muted">Overview of your content management system.</p>

<div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
    @foreach (['users' => 'Users', 'roles' => 'Roles', 'permissions' => 'Permissions', 'medias' => 'Media'] as $key => $label)
        <div class="rounded-md border border-wp-border bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-wp-muted">{{ $label }}</p>
            <p class="mt-2 text-3xl font-semibold text-wp-ink">{{ number_format($stats[$key]) }}</p>
        </div>
    @endforeach
</div>

<div class="mt-8">
    <h2 class="mb-3 text-sm font-semibold text-wp-ink">Building tables · column reference</h2>
    @include('rjcms::admin.breads._column-types-help')
</div>
@endsection
