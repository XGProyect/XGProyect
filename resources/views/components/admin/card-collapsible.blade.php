{{--
    Admin collapsible card (Alpine-driven, replaces Bootstrap accordion).

    Usage:
        <x-admin.card-collapsible id="collapseStats" title="Statistics" :open="true" icon="server">
            <p>Content goes here</p>
        </x-admin.card-collapsible>

    Props mirror the legacy SB Admin component so existing pages don't need to change.
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

<div class="adm-card mb-4" id="{{ $id }}" x-data="{ open: @js((bool) $open) }">
    <div class="adm-card-header" style="cursor: pointer;" @click="open = !open">
        <h2 class="adm-card-title">
            @if ($icon)<i data-lucide="{{ $icon }}"></i>@endif
            <span>{{ $title }}</span>
            @if (!is_null($badge))
                <span class="adm-badge adm-badge-neutral">{{ $badge }}</span>
            @endif
        </h2>
        <button type="button" class="adm-card-collapse-toggle" aria-label="Toggle">
            <i data-lucide="chevron-down"
               :style="open ? '' : 'transform: rotate(-90deg);'"
               style="transition: transform 0.15s ease;"></i>
        </button>
    </div>
    <div x-show="open" x-collapse>
        <div @class(['adm-card-body', '!p-0' => $flush])>
            {!! $slot !!}
        </div>
    </div>
</div>
