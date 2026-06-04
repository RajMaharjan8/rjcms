@extends('rjcms::layouts.app')

@section('title', 'Blog · ' . setting('site.name', config('app.name')))

@section('body')
<div class="mx-auto max-w-3xl px-4 py-12 sm:px-6">
    <h1 class="text-3xl font-bold tracking-tight text-gray-900">Blog</h1>

    <div class="mt-8 flex flex-col gap-8">
        @forelse ($posts as $post)
            <article class="border-b border-gray-200 pb-8">
                @if ($post->featuredImage)
                    <a href="{{ route('blog.show', $post->slug) }}">
                        <img src="{{ $post->featuredImage->thumbnail_url }}" alt="{{ $post->title }}" loading="lazy"
                             class="mb-4 h-48 w-full rounded-lg object-cover ring-1 ring-gray-200">
                    </a>
                @endif
                <h2 class="text-xl font-semibold text-gray-900">
                    <a href="{{ route('blog.show', $post->slug) }}" class="hover:underline">{{ $post->title }}</a>
                </h2>
                @if ($post->excerpt)
                    <p class="mt-2 text-gray-600">{{ $post->excerpt }}</p>
                @endif
                <a href="{{ route('blog.show', $post->slug) }}" class="mt-3 inline-block text-sm font-medium text-indigo-600 hover:text-indigo-500">Read more →</a>
            </article>
        @empty
            <p class="text-gray-500">No posts published yet.</p>
        @endforelse
    </div>

    <div class="mt-8">{{ $posts->links() }}</div>
</div>
@endsection
