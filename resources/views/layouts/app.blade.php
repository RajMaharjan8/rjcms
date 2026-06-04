<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <link rel="stylesheet" href="{{ rjcms_asset('app.css') }}">
    <script src="{{ rjcms_asset('app.js') }}" defer></script>
    @livewireStyles
</head>
<body class="min-h-screen bg-gray-100 font-sans text-gray-900 antialiased">
    @yield('body')
    @livewireScripts
    @stack('scripts')
</body>
</html>
