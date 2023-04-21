<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="admin.php?page=home">
        <div class="sidebar-brand-icon">
            <img src="https://xgproyect.org/wp-content/uploads/2019/10/xgp-new-logo-white.png" alt="XG Proyect Logo"
                title="XG Proyect" width="150px">
        </div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        {{ __('admin/menu.general') }}
    </div>

    @foreach ($sections as $sectionTitle => $menuItems)
    <!-- Nav Item - Pages Collapse Menu -->
    <li class="nav-item{{ $activeBlock === $sectionTitle ? ' show' : '' }}">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapse{{ $loop->iteration }}" aria-expanded="true"
            aria-controls="collapse{{ $loop->iteration }}">
            <i class="fas fa-fw {{ $menuItems['icon'] }}"></i>
            <span>{{ __('admin/menu.' . $sectionTitle) }}</span>
        </a>
        <div id="collapse{{ $loop->iteration }}" class="collapse{{ $activeBlock === $sectionTitle ? ' active' : '' }}" aria-labelledby="heading{{ $loop->iteration }}" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                @foreach ($menuItems['items'] as $item => $data)
                <a class="collapse-item{{ ($item === $activePage) ? ' active' : '' }}" href="admin.php?page={{ $item }}" @isset($data[0]) {!! $data[0] !!} @endisset>
                    {{ __('admin/menu.' . $item) }}
                </a>
                @endforeach
            </div>
        </div>
    </li>
    @endforeach

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>