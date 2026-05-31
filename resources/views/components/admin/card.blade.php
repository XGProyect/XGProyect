{{--
    Admin card component (Tailwind/lucide rewrite).

    Usage:
        <x-admin.card title="Card title" icon="users" :badge="count($items)">
            (optional right-side header action via <x-slot name="action">)
            (card body content goes in the default slot)
        </x-admin.card>

    Props:
        title   (string)       - card header label (rendered as raw HTML via {!! !!})
        icon    (string|null)  - lucide icon name (e.g. "users")
        badge   (mixed|null)   - value shown in a badge next to the title; hidden when null
        flush   (bool)         - when true, removes body padding for embedded tables
--}}
@props([
    'title',
    'icon'  => null,
    'badge' => null,
    'flush' => false,
])

<div class="adm-card mb-4">
    <div class="adm-card-header">
        <h2 class="adm-card-title">
            @if ($icon)
                <i data-lucide="{{ $icon }}"></i>
            @endif
            <span>{!! $title !!}</span>
            @if (!is_null($badge))
                <span class="adm-badge adm-badge-neutral">{{ $badge }}</span>
            @endif
        </h2>
        @isset($action)
            {!! $action !!}
        @endisset
    </div>
    <div @class(['adm-card-body', '!p-0' => $flush])>
        {!! $slot !!}
    </div>
</div>
