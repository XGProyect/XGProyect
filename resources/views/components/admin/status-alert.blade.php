{{--
    Admin status alert (Tailwind rewrite). View-data driven; for session
    flashes use <x-alert/>.

    Maps the legacy Bootstrap class names (alert-success/warning/danger)
    to the new .adm-alert-* variants so controllers don't need to change.
--}}
@props(['message', 'style', 'type'])

@php
    $variant = match ($style) {
        'alert-danger'  => ['cls' => 'adm-alert-danger',  'icon' => 'octagon-x'],
        'alert-warning' => ['cls' => 'adm-alert-warning', 'icon' => 'triangle-alert'],
        'alert-info'    => ['cls' => 'adm-alert-info',    'icon' => 'info'],
        default         => ['cls' => 'adm-alert-success', 'icon' => 'circle-check'],
    };
@endphp

<div class="adm-alert {{ $variant['cls'] }}">
    <i data-lucide="{{ $variant['icon'] }}"></i>
    <div>
        @if (!empty($type))
            <div class="adm-alert-title">{{ $type }}</div>
        @endif
        <div class="adm-alert-body">{!! $message !!}</div>
    </div>
</div>
