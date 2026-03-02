{{--
    Admin empty-state component.

    Usage:
        <x-admin.empty-state icon="fas fa-scroll" message="No entries found." />

    Props:
        icon     (string)      - FontAwesome class for the large icon, e.g. "fas fa-scroll"
        message  (string)      - Text to display below the icon
        size     (string)      - Padding size: "sm" (py-3 p-3), "md" (p-4, default), "lg" (py-5)
--}}
@props([
    'icon',
    'message',
    'size' => 'md',
])

@php
    $padding = match($size) {
        'sm' => 'p-3',
        'lg' => 'py-5 px-4',
        default => 'p-4',
    };
@endphp

<div class="{{ $padding }} text-center text-muted">
    <i class="{{ $icon }} fa-3x mb-3 d-block text-gray-300"></i>
    {{ $message }}
</div>
