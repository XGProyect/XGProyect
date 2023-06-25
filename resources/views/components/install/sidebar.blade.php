<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('install.index') }}">
        <div class="sidebar-brand-icon">
            <img src="https://xgproyect.org/wp-content/uploads/2019/10/xgp-new-logo-white.png" alt="XG Proyect Logo"
                title="XG Proyect" width="150px">
        </div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        {{ __('install/install.sidebar_heading') }}
    </div>

    @foreach ($steps as $step => $details)
    <!-- Nav Item - Dashboard -->
    <li class="nav-item{{ (('install.step.' . $step) === $activeStep) ? ' active' : '' }}">
        <a class="nav-link">
            <i class="{{ $details['icon'] }}"></i>
            <span>{{ __('install/install.' . $step) }}</span>
        </a>
    </li>
    @endforeach

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">
</ul>