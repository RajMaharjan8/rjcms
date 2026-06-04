@extends('rjcms::layouts.app')

@section('title', 'Log In · ' . setting('site.name', config('app.name')))

@section('body')
@php($siteName = setting('site.name', config('app.name')))
@php($logo = filled(setting('site.logo')) ? \Rjcodes\Rjcms\Models\Media::find(setting('site.logo')) : null)

<div class="flex min-h-screen flex-col items-center justify-center bg-wp-bg px-4 py-12">
    <div class="w-full max-w-80">
        {{-- Branding (WordPress-style logo above the form) --}}
        <div class="mb-6 text-center">
            @if ($logo && $logo->isImage())
                <img src="{{ $logo->url }}" alt="{{ $siteName }}" class="mx-auto mb-2 max-h-16 w-auto">
            @endif
            <h1 class="text-xl font-semibold text-wp-ink">{{ $siteName }}</h1>
        </div>

        <div class="rounded border border-wp-border bg-white px-6 py-7 shadow-md">
            @if ($errors->any())
                <div class="mb-5 rounded border-l-4 border-red-500 bg-red-50 px-3 py-2 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.store') }}" class="flex flex-col gap-4">
                @csrf

                <div class="flex flex-col gap-1.5">
                    <label for="email" class="text-sm font-semibold text-wp-ink">Email Address</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}"
                           required autofocus autocomplete="email"
                           class="w-full rounded border border-wp-border bg-white px-3 py-2 text-sm text-wp-ink shadow-sm transition focus:border-wp-blue focus:ring-1 focus:ring-wp-blue focus:outline-none">
                </div>

                <div class="flex flex-col gap-1.5">
                    <label for="password" class="text-sm font-semibold text-wp-ink">Password</label>
                    <input id="password" name="password" type="password"
                           required autocomplete="current-password"
                           class="w-full rounded border border-wp-border bg-white px-3 py-2 text-sm text-wp-ink shadow-sm transition focus:border-wp-blue focus:ring-1 focus:ring-wp-blue focus:outline-none">
                </div>

                <div class="mt-1 flex items-center justify-between gap-3">
                    <label class="flex items-center gap-2 text-sm text-wp-muted">
                        <input type="checkbox" name="remember"
                               class="rounded border-wp-border text-wp-blue focus:ring-wp-blue">
                        Remember Me
                    </label>

                    <button type="submit"
                            class="rounded border border-wp-blue bg-wp-blue px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-wp-blue-hover focus:ring-2 focus:ring-wp-blue/40 focus:outline-none">
                        Log In
                    </button>
                </div>
            </form>
        </div>

        <p class="mt-5 text-center text-sm">
            <a href="{{ url('/') }}" class="text-wp-muted hover:text-wp-blue">&larr; Back to {{ $siteName }}</a>
        </p>
    </div>
</div>
@endsection
