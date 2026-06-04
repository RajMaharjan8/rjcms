{{--
    Example static page rendered from a Blade file (no database record).
    Routed in routes/public.php via:  Route::view('/contact', 'pages.contact')
    The form posts to the ContactController. Copy this file to
    resources/views/pages/{slug}.blade.php and add a route to create more pages.
--}}
@extends('rjcms::layouts.app')

@section('title', 'Contact · ' . setting('site.name', config('app.name')))

@section('body')
<div class="mx-auto max-w-xl px-4 py-12 sm:px-6">
    <h1 class="text-3xl font-bold tracking-tight text-gray-900">Contact</h1>

    @if (session('status'))
        <div class="mt-6 rounded border-l-4 border-green-600 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('contact.send') }}" class="mt-6 flex flex-col gap-4">
        @csrf

        <div class="flex flex-col gap-1.5">
            <label for="name" class="text-sm font-medium text-gray-700">Name</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required
                   class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none">
            @error('name') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="flex flex-col gap-1.5">
            <label for="email" class="text-sm font-medium text-gray-700">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required
                   class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none">
            @error('email') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="flex flex-col gap-1.5">
            <label for="message" class="text-sm font-medium text-gray-700">Message</label>
            <textarea id="message" name="message" rows="5" required
                      class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none">{{ old('message') }}</textarea>
            @error('message') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="w-fit rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
            Send message
        </button>
    </form>
</div>
@endsection
