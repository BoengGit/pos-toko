@props(['status'])

@php
    $color = match ($status) {
        'aman' => 'bg-green-100 text-green-700',
        'menipis' => 'bg-yellow-100 text-yellow-700',
        'habis' => 'bg-red-100 text-red-700',
        default => 'bg-gray-100 text-gray-700',
    };
@endphp

<span {{ $attributes->merge(['class' => "px-2 py-1 text-xs font-medium rounded-full $color"]) }}>
    {{ $slot }}
</span>
