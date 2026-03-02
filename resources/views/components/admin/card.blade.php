{{--
    Admin card component.

    Usage:
        <x-admin.card title="Card title" icon="fas fa-users" :badge="count($items)">
            (optional right-side header action via <x-slot name="action">)
            (card body content goes in the default slot)
        </x-admin.card>

    Props:
        title   (string)       - card header label (rendered as raw HTML via {!! !!})
        icon    (string|null)  - FontAwesome class, e.g. "fas fa-users"
        badge   (mixed|null)   - value shown in a badge next to the title; hidden when null
        flush   (bool)         - when true, removes card-body padding (p-0) for tables
--}}
@props([
    'title',
    'icon'  => null,
    'badge' => null,
    'flush' => false,
])

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">
            @if ($icon)
                <i class="{{ $icon }} mr-1"></i>
            @endif
            {!! $title !!}
            @if (!is_null($badge))
                <span class="badge badge-secondary ml-1">{{ $badge }}</span>
            @endif
        </h6>
        @isset($action)
            {{ $action }}
        @endisset
    </div>
    <div class="{{ $flush ? 'card-body p-0' : 'card-body' }}">
        {{ $slot }}
    </div>
</div>
