{{--
    Example custom post template. To use it, edit a blog post and set its
    "Template" field to:  blog.templates.featured
    (Put your own templates anywhere under resources/views/ and reference them
    in dot notation — this file is just a starting point to copy.)
    The published $post is available with all its fields and ->categories.
--}}
@extends('rjcms::layouts.app')

@section('title', $post->title . ' · ' . setting('site.name', config('app.name')))

@section('body')
<article>
    @if ($post->featuredImage)
        <div class="relative h-[28rem] w-full overflow-hidden">
            <img src="{{ $post->featuredImage->url }}" alt="{{ $post->title }}" class="h-full w-full object-cover">
            <div class="absolute inset-0 flex items-end bg-gradient-to-t from-black/70 to-transparent">
                <div class="mx-auto w-full max-w-3xl px-4 pb-10 sm:px-6">
                    <h1 class="text-4xl font-bold tracking-tight text-white">{{ $post->title }}</h1>
                </div>
            </div>
        </div>
    @else
        <div class="mx-auto max-w-3xl px-4 pt-12 sm:px-6">
            <h1 class="text-4xl font-bold tracking-tight text-gray-900">{{ $post->title }}</h1>
        </div>
    @endif

    <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6">
        <p class="mb-4 text-sm text-gray-500">
            <a href="{{ route('blog.index') }}" class="hover:underline">Blog</a>
            @if ($post->published_at) · {{ $post->published_at->format('M j, Y') }} @endif
        </p>

        @if ($post->categories->isNotEmpty())
            <div class="mb-6 flex flex-wrap gap-2">
                @foreach ($post->categories as $category)
                    <a href="{{ route('blog.category', $category) }}"
                       class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 hover:bg-gray-200">{{ $category->name }}</a>
                @endforeach
            </div>
        @endif

        @if ($post->body)
            <div class="rich-content text-lg leading-relaxed text-gray-800">{!! $post->body !!}</div>
        @endif
    </div>
</article>
@endsection
