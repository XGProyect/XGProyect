<header class="adm-topbar">
    <button type="button"
            class="adm-topbar-toggle"
            data-action="adm-toggle-sidebar"
            title="Toggle sidebar"
            aria-label="Toggle sidebar">
        <i data-lucide="menu"></i>
    </button>

    <div class="adm-topbar-title">@yield('page-title', 'Admin Panel')</div>

    <form action="{{ route('admin.search') }}" method="GET" class="adm-topbar-search">
        <i data-lucide="search"></i>
        <input type="text"
               name="term"
               placeholder="{{ __('admin/navigation.nv_search_for') }}"
               autocomplete="off">
    </form>

    <div class="adm-topbar-spacer"></div>

    <button type="button"
            class="adm-topbar-bell"
            data-action="adm-toggle-theme"
            title="Toggle theme"
            aria-label="Toggle theme">
        <i data-lucide="moon" data-theme-icon-dark></i>
        <i data-lucide="sun" data-theme-icon-light style="display: none;"></i>
    </button>

    <a href="https://github.com/XGProyect/XGProyect/releases"
       target="_blank"
       rel="noopener"
       class="adm-topbar-bell"
       title="{{ __('admin/navigation.nv_new_download') }}">
        <i data-lucide="bell"></i>
    </a>

    <a href="{{ url('game.php?page=overview') }}" class="adm-btn adm-btn-ghost adm-btn-sm" title="Open game">
        <i data-lucide="external-link"></i>
        <span>Game</span>
    </a>

    <div x-data="{ open: false }" style="position: relative;">
        <button type="button"
                class="adm-topbar-user"
                @click="open = !open"
                @click.outside="open = false">
            <span class="adm-topbar-user-avatar">{{ strtoupper(mb_substr($username, 0, 1)) }}</span>
            <span class="adm-topbar-user-name">{{ $username }}</span>
            <i data-lucide="chevron-down"></i>
        </button>

        <div x-show="open"
             x-transition.opacity
             class="adm-topbar-menu"
             style="display: none">
            <a href="{{ route('admin.logout') }}">
                <i data-lucide="log-out"></i>
                <span>{{ __('admin/navigation.nv_logout') }}</span>
            </a>
        </div>
    </div>
</header>
