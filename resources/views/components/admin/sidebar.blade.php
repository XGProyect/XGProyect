@php
    /**
     * Icon map per section — lucide names. Keep in sync with sections in
     * App\View\Components\Admin\Sidebar so each block lights up with a
     * meaningful icon at the top of its group header (when collapsed) and
     * next to each item.
     */
    $sectionIcons = [
        'configuration' => 'settings',
        'information' => 'info',
        'edition' => 'pencil',
        'tools' => 'wrench',
        'maintenance' => 'brush',
    ];

    /** Per-item icons — lucide names. */
    $itemIcons = [
        // configuration
        'server' => 'server',
        'mailing' => 'mail',
        'modules' => 'boxes',
        'planets' => 'globe',
        'expeditions' => 'compass',
        'registration' => 'user-plus',
        'statistics' => 'bar-chart-3',
        'premium' => 'star',
        // information
        'tasks' => 'list-checks',
        'errors' => 'triangle-alert',
        'fleets' => 'rocket',
        'messages' => 'message-square',
        // edition
        'users' => 'users',
        'bots' => 'bot',
        'alliances' => 'shield',
        'languages' => 'languages',
        'changelog' => 'history',
        'permissions' => 'key-round',
        // tools
        'backup' => 'database',
        'announcement' => 'megaphone',
        'ban' => 'gavel',
        'rebuildhighscores' => 'trophy',
        'update' => 'arrow-up-from-line',
        // maintenance
        'repair' => 'hammer',
        'reset' => 'rotate-ccw',
    ];
@endphp

<aside class="adm-sidebar">
    <a href="{{ url('admin/home') }}" class="adm-sidebar-brand">
        <span class="adm-sidebar-brand-mark">XG</span>
        <span class="adm-sidebar-text">
            <div class="adm-sidebar-brand-title">XG Proyect</div>
            <div class="adm-sidebar-brand-sub">Admin · v{{ config('version.files') }}</div>
        </span>
    </a>

    <nav class="adm-sidebar-nav">
        @foreach ($sections as $sectionTitle => $menuItems)
            <div class="adm-sidebar-section adm-sidebar-text">
                {{ __('admin/menu.' . $sectionTitle) }}
            </div>

            @foreach ($menuItems['items'] as $item => $data)
                @php($isActive = $item === $activePage)
                <a href="{{ url("admin/{$item}") }}"
                   data-page="{{ $item }}"
                   class="adm-sidebar-link{{ $isActive ? ' is-active' : '' }}"
                   @isset($data[0]) {!! $data[0] !!} @endisset>
                    <i data-lucide="{{ $itemIcons[$item] ?? 'circle' }}"></i>
                    <span class="adm-sidebar-text">{{ __("admin/menu.{$item}") }}</span>
                </a>
            @endforeach
        @endforeach
    </nav>

    <div class="adm-sidebar-foot adm-sidebar-text">
        XG Proyect v{{ config('version.files') }}
    </div>
</aside>
