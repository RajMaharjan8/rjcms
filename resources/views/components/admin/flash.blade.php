@if (session('status'))
    <div class="mb-6 rounded border-l-4 border-green-600 bg-white px-4 py-3 text-sm text-wp-ink shadow-sm">
        {{ session('status') }}
    </div>
@endif

@if (session('error'))
    <div class="mb-6 rounded border-l-4 border-red-500 bg-white px-4 py-3 text-sm text-wp-ink shadow-sm">
        {{ session('error') }}
    </div>
@endif
