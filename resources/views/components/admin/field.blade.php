@props(['label' => null, 'name', 'hint' => null])

<div class="flex flex-col gap-1.5">
    @if ($label)
        <label for="{{ $name }}" class="text-sm font-medium text-gray-700">{{ $label }}</label>
    @endif
    {{ $slot }}
    @if ($hint)
        <p class="text-xs text-gray-500">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
