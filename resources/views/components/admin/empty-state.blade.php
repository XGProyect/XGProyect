{{--
    Admin empty-state component (Tailwind rewrite).

    Usage:
        <x-admin.empty-state icon="scroll" message="No entries found." />

    Props:
        icon     (string)  - lucide icon name (e.g. "scroll", "inbox")
        message  (string)  - text shown below the icon
        size     (string)  - "sm" | "md" (default) | "lg"
--}}
@props([
    'icon',
    'message',
    'size' => 'md',
])

@php
    $padding = match ($size) {
        'sm' => '12px',
        'lg' => '40px 16px',
        default => '24px 16px',
    };
@endphp

<div style="padding: {{ $padding }}; text-align: center; color: #64748b;">
    <i data-lucide="{{ $icon }}" style="width: 40px; height: 40px; margin: 0 auto 12px; display: block; color: #475569; opacity: 0.6;"></i>
    <p style="margin: 0; font-size: 13px;">{{ $message }}</p>
</div>
