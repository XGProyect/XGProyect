{{--
    Admin page header component.

    Usage:
        <x-admin.page-header
            title="Page Title"
            subtitle="Optional description shown below the title."
        >
            (optional right-side action via <x-slot name="action">)
        </x-admin.page-header>

    Props:
        title     (string)      - page heading text
        subtitle  (string|null) - optional paragraph below the heading (rendered as raw HTML)
--}}
@props([
    'title',
    'subtitle' => null,
])

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">{{ $title }}</h1>
    @isset($action)
        {{ $action }}
    @endisset
</div>
@if ($subtitle)
    <p class="mb-4 text-gray-600">{!! $subtitle !!}</p>
@endif
