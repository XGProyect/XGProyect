{{--
    Admin page header (Tailwind rewrite).

    Usage:
        <x-admin.page-header title="Page Title" subtitle="Optional description">
            <x-slot name="action">
                <a href="…" class="adm-btn adm-btn-primary">Action</a>
            </x-slot>
        </x-admin.page-header>
--}}
@props([
    'title',
    'subtitle' => null,
])

<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title">{{ $title }}</h1>
        @if ($subtitle)
            <p class="adm-page-subtitle">{!! $subtitle !!}</p>
        @endif
    </div>
    @isset($action)
        <div class="adm-page-actions">{!! $action !!}</div>
    @endisset
</div>
