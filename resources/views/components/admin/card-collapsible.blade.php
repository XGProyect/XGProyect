{{--
    Admin collapsible card component (Bootstrap accordion pattern).

    Usage:
        <x-admin.card-collapsible id="collapseStats" title="Statistics" :open="true">
            <p>Content goes here</p>
        </x-admin.card-collapsible>

    Props:
        id      (string)           - unique id for the collapse target (no #)
        title   (string)           - card header label
        icon    (string|null)      - FontAwesome class shown before the title
        badge   (int|string|null)  - optional count badge shown next to the title
        open    (bool)             - whether the panel starts expanded (default true)
        flush   (bool)             - removes card-body padding (p-0) for full-bleed tables
        chevron (bool)             - show the collapse chevron arrow (default false)
--}}
@props([
    'id',
    'title',
    'icon'    => null,
    'badge'   => null,
    'open'    => true,
    'flush'   => false,
    'chevron' => false,
])

<div class="card shadow mb-4">
    <a href="#{{ $id }}" class="d-block card-header py-3" data-toggle="collapse"
        role="button" aria-expanded="{{ $open ? 'true' : 'false' }}" aria-controls="{{ $id }}">
        <h6 class="m-0 font-weight-bold text-primary">
            @if ($chevron)<i class="fas fa-chevron-down fa-xs mr-2 collapse-icon"></i>@endif
            @if ($icon)<i class="{{ $icon }} mr-1"></i>@endif
            {{ $title }}
            @if (!is_null($badge))
                <span class="badge badge-secondary ml-1">{{ $badge }}</span>
            @endif
        </h6>
    </a>
    <div class="collapse {{ $open ? 'show' : '' }}" id="{{ $id }}">
        <div class="{{ $flush ? 'card-body p-0' : 'card-body' }}">
            {!! $slot !!}
        </div>
    </div>
</div>
