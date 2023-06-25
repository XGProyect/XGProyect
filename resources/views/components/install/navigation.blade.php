<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

    <!-- Sidebar Toggle (Topbar) -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>

    <!-- Topbar Navbar -->
    <ul class="navbar-nav ml-auto">
        <div class="topbar-divider d-none d-sm-block"></div>

        <!-- Nav Item - Languages -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="languagesDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600">
                    <i class="fas fa-language"></i>
                    {{ Str::upper($language) }}
                </span>
            </a>
            <!-- Dropdown - Languages -->
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="languagesDropdown">
            @foreach ($languages as $lang)
                @if ($lang !== $language)
                <a class="dropdown-item" href="{{ route('install.set.locale', ['locale' => $lang]) }}">
                    <i class="fas fa-language"></i>
                    {{ Str::upper($lang) }}
                </a>
                @endif
            @endforeach
            </div>
        </li>
    </ul>
</nav>