{{-- ============================================================
     ADMIN — main layout. Sidebar + topbar + content slot.
     Built with Tailwind 4 + Alpine via the Vite pipeline
     (resources/css/admin.css + resources/js/admin.js).
     Replaces the old SB Admin 2 / Bootstrap 4 / jQuery stack.
     ============================================================ --}}
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="XG Proyect | Admin CP">
    <meta name="author" content="XG Proyect">

    <title>XG Proyect | Admin CP</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/admin.css', 'resources/js/admin.js'])

    @stack('styles')
</head>
<body data-sidebar-collapsed="false" data-sidebar-mobile-open="false">

    {{-- Mobile overlay (closes sidebar when tapped) --}}
    <div class="adm-mobile-overlay" data-action="adm-close-sidebar"></div>

    <x-admin.sidebar />

    <div class="adm-shell">
        <x-admin.navigation />

        <main class="adm-content">
            @yield('content')
        </main>

        <x-admin.footer />
    </div>

    @stack('scripts')
</body>
</html>
