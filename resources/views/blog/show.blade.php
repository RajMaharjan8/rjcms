@extends('rjcms::layouts.app')

@section('title', $post->title . ' · ' . setting('site.name', config('app.name')))

@section('body')
<div class="mx-auto max-w-3xl px-4 py-12 sm:px-6">
    <article>
        @if ($post->featuredImage)
            <img src="{{ $post->featuredImage->url }}" alt="{{ $post->title }}"
                 class="mb-8 w-full rounded-xl object-cover ring-1 ring-gray-200">
        @endif

        <p class="mb-2 text-sm text-gray-500">
            <a href="{{ route('blog.index') }}" class="hover:underline">Blog</a>
            @if ($post->published_at) · {{ $post->published_at->format('M j, Y') }} @endif
        </p>

        <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ $post->title }}</h1>

        @if ($post->categories->isNotEmpty())
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($post->categories as $category)
                    <a href="{{ route('blog.category', $category) }}"
                       class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 hover:bg-gray-200">{{ $category->name }}</a>
                @endforeach
            </div>
        @endif

        @if ($post->body)
            <div class="rich-content mt-6 leading-relaxed text-gray-800">{!! $post->body !!}</div>
        @endif
    </article>
</div>
@endsection
