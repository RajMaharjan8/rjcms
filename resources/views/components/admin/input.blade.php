@props(['name', 'type' => 'text', 'value' => null])

<input {{ $attributes->merge([
    'id' => $name,
    'name' => $name,
    'type' => $type,
    'class' => 'w-full rounded border border-wp-border bg-white px-3 py-1.5 text-sm text-wp-ink shadow-sm transition focus:border-wp-blue focus:ring-1 focus:ring-wp-blue focus:outline-none',
]) }}
    @unless (in_array($type, ['file', 'password'])) value="{{ old($name, $value) }}" @endunless>
