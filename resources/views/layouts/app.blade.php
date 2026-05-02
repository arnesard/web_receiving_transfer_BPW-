<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0021b3">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Penerimaan Produksi v2.0</title>

    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/icon-512.png') }}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <style>
        :root {
            --header-height: 64px;
            --primary: #0021b3;
            --primary-light: #3b5de7;
            --sky-600: #0284c7;
            --slate-500: #64748b;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: ui-sans-serif, system-ui, -apple-system, sans-serif;
            background-color: #f8fafc;
            /* Lighter background matching the cards */
            margin: 0;
            padding-top: var(--header-height);
        }

        /* ═══════════════════════════════════════ */
        /* HEADER BAR                              */
        /* ═══════════════════════════════════════ */
        .header-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--header-height);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 1000;
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            /* Soft shadow matching floating cards */
            border-bottom: 1px solid #f1f5f9;
        }

        /* Logo / Brand */
        .header-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            margin-right: 2.5rem;
            flex-shrink: 0;
        }

        .header-brand img {
            height: 32px;
            width: auto;
            /* Invert removed; assumes original logo looks good on white */
        }

        .header-brand-text h1 {
            font-size: 11px;
            font-weight: 700;
            color: #0021b3;
            /* Deep GT Blue */
            margin: 0;
            letter-spacing: -0.01em;
            text-transform: uppercase;
        }

        .header-brand-text p {
            font-size: 1rem;
            color: #0021b3;
            /* Deep GT Blue */
            margin: 0;
            font-weight: 900;
            text-transform: uppercase;
            line-height: 1.1;
            letter-spacing: -0.02em;
        }

        /* Navigation links (desktop) */
        .header-nav {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
        }

        .header-nav-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0.55rem 1rem;
            color: #64748b;
            text-decoration: none;
            border-radius: 99px;
            /* Pill shape matching modern buttons */
            font-size: 13.5px;
            font-weight: 600;
            transition: all 0.2s ease;
            white-space: nowrap;
            position: relative;
        }

        .header-nav-link svg,
        .header-nav-link i {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            stroke-width: 2.5px;
        }

        .header-nav-link:hover {
            color: #1e293b;
            background: #f1f5f9;
        }

        .header-nav-link.active {
            background: rgba(13, 110, 253, 0.1);
            /* Primary blue opacity matching dashboard badges */
            color: #0d6efd;
        }

        /* Separator dot between nav groups - hidden for clean look */
        .nav-dot {
            display: none;
        }

        /* User area (right side) */
        .header-user {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-left: auto;
            flex-shrink: 0;
        }

        .header-user-info {
            text-align: right;
            display: flex;
            align-items: center;
        }

        .header-user-name {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
        }

        .header-avatar {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            /* Smoother corner matching .rounded-3 */
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0d6efd;
            /* Primary blue */
            font-weight: 800;
            font-size: 15px;
        }

        .btn-header-logout {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            background: transparent;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            color: #ef4444;
            /* Soft Red */
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-header-logout:hover {
            background: #fef2f2;
            border-color: #fecaca;
        }

        .btn-header-logout svg,
        .btn-header-logout i {
            width: 18px;
            height: 18px;
        }

        /* Mobile hamburger */
        .header-hamburger {
            display: none;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            cursor: pointer;
            align-items: center;
            justify-content: center;
            color: #1e293b;
            margin-left: auto;
            padding: 0;
            transition: all 0.2s;
        }

        .header-hamburger:hover {
            background: #f1f5f9;
        }

        .header-hamburger svg {
            width: 22px;
            height: 22px;
        }

        /* Mobile dropdown menu */
        .mobile-menu-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(4px);
            z-index: 1100;
        }

        .mobile-menu-overlay.active {
            display: block;
        }

        .mobile-menu {
            display: none;
            position: fixed;
            top: var(--header-height);
            left: 0;
            right: 0;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            z-index: 1200;
            padding: 0.75rem;
            animation: slideDown 0.25s ease;
        }

        .mobile-menu.active {
            display: block;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .mobile-nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.85rem 1rem;
            color: #64748b;
            text-decoration: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            transition: 0.15s;
        }

        .mobile-nav-link:hover {
            background: #f8fafc;
            color: #1e293b;
        }

        .mobile-nav-link.active {
            background: rgba(13, 110, 253, 0.08);
            color: #0d6efd;
        }

        .mobile-nav-link svg,
        .mobile-nav-link i {
            width: 18px;
            height: 18px;
            stroke-width: 2.5px;
        }

        .mobile-menu-divider {
            height: 1px;
            background: #f1f5f9;
            margin: 0.5rem 0;
        }

        .mobile-user-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1rem;
            background: #f8fafc;
            border-radius: 12px;
            margin-top: 0.5rem;
        }

        .mobile-user-name {
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
        }

        .btn-mobile-logout {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            color: #ef4444;
            cursor: pointer;
        }

        .btn-mobile-logout:hover {
            background: #fef2f2;
            border-color: #fecaca;
        }

        /* ═══════════════════════════════════════ */
        /* MAIN CONTENT                            */
        /* ═══════════════════════════════════════ */
        #main-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1.75rem 2.5rem;
            min-height: calc(100vh - var(--header-height));
        }

        /* ═══════════════════════════════════════ */
        /* RESPONSIVE                              */
        /* ═══════════════════════════════════════ */
        @media (max-width: 992px) {
            .header-nav {
                display: none;
            }

            .header-user-info {
                display: none;
            }

            .header-avatar {
                display: none;
            }

            .btn-header-logout {
                display: none;
            }

            .header-hamburger {
                display: flex;
            }

            #main-content {
                padding: 1.25rem 1rem;
            }
        }

        @media (min-width: 993px) {

            .mobile-menu,
            .mobile-menu-overlay {
                display: none !important;
            }
        }

        /* ═══════════════════════════════════════ */
        /* PRINT                                   */
        /* ═══════════════════════════════════════ */
        @media print {

            .header-bar,
            .mobile-menu,
            .mobile-menu-overlay {
                display: none !important;
            }

            body {
                padding-top: 0;
            }

            #main-content {
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100%;
            }

            body {
                background-color: white !important;
            }
        }
    </style>
    @stack('styles')
</head>

<body>

    {{-- ═══ HEADER BAR ═══ --}}
    <header class="header-bar">
        {{-- Brand --}}
        <a href="{{ route('dashboard') }}" class="header-brand">
            <img src="{{ asset('images/logo-gt.png') }}" alt="GT">
            <div class="header-brand-text">
                <p>PT Gajah Tunggal Tbk</p>
                <h1>Gudang Ban B</h1>
            </div>
        </a>

        {{-- Desktop Navigation --}}
        <nav class="header-nav">
            <a href="{{ route('dashboard') }}"
                class="header-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i data-lucide="layout-dashboard"></i> <span>Dashboard</span>
            </a>

            <a href="{{ route('input.form') }}"
                class="header-nav-link {{ request()->routeIs('input.*') ? 'active' : '' }}">
                <i data-lucide="clipboard-list"></i> <span>Input</span>
            </a>

            @if (auth()->user()->isAdmin())
                <a href="{{ route('employees.index') }}"
                    class="header-nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                    <i data-lucide="users"></i> <span>Karyawan</span>
                </a>
            @endif

            @if (auth()->user()->isAdmin())
                <a href="{{ route('reports.index') }}"
                    class="header-nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <i data-lucide="files"></i> <span>Laporan</span>
                </a>
                <a href="{{ route('users.index') }}"
                    class="header-nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i data-lucide="shield"></i> <span>User</span>
                </a>
            @endif
        </nav>

        {{-- User area (desktop) --}}
        <div class="header-user">
            <div class="header-user-info">
                <div class="header-user-name">{{ auth()->user()->name ?? 'Admin' }}</div>
            </div>
            <div class="header-avatar">
                {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
            </div>
            <a href="{{ route('pilihmenu.index') }}" class="btn-header-logout" title="Kembali ke Menu">
                <i data-lucide="home"></i>
            </a>
        </div>

        {{-- Hamburger (mobile) --}}
        <button class="header-hamburger" id="mobileMenuBtn" onclick="toggleMobileMenu()">
            <svg id="hamburgerIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
            <svg id="closeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                style="display:none;">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </header>

    {{-- Mobile overlay --}}
    <div class="mobile-menu-overlay" id="mobileOverlay" onclick="toggleMobileMenu()"></div>

    {{-- Mobile dropdown menu --}}
    <div class="mobile-menu" id="mobileMenu">
        <a href="{{ route('dashboard') }}"
            class="mobile-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i data-lucide="layout-dashboard"></i> Dashboard
        </a>
        <a href="{{ route('input.form') }}"
            class="mobile-nav-link {{ request()->routeIs('input.*') ? 'active' : '' }}">
            <i data-lucide="clipboard-list"></i> Input
        </a>
        <div class="mobile-menu-divider"></div>
        @if (auth()->user()->isAdmin())
            <a href="{{ route('employees.index') }}"
                class="mobile-nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                <i data-lucide="users"></i> Karyawan
            </a>
        @endif
        <a href="{{ route('overtime.index') }}"
            class="mobile-nav-link {{ request()->routeIs('overtime.*') ? 'active' : '' }}">
            <i data-lucide="clock"></i> Lembur
        </a>
        @if (auth()->user()->isAdmin())
            <a href="{{ route('reports.index') }}"
                class="mobile-nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <i data-lucide="files"></i> Laporan
            </a>
            <a href="{{ route('users.index') }}"
                class="mobile-nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i data-lucide="shield"></i> Kelola User
            </a>
        @endif
        <div class="mobile-menu-divider"></div>
        <div class="mobile-user-section">
            <a href="{{ route('pilihmenu.index') }}" class="btn-header-logout" title="Kembali ke Menu">
                <i data-lucide="home"></i>
            </a>
        </div>
    </div>

    {{-- ═══ MAIN CONTENT ═══ --}}
    <main id="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <h3 class="fw-bold">@yield('page_title')</h3>
        </div>

        @yield('content')
    </main>

    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/lucide.min.js') }}"></script>
    <script>
        lucide.createIcons();

        // Mobile menu toggle
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            const overlay = document.getElementById('mobileOverlay');
            const hamburger = document.getElementById('hamburgerIcon');
            const close = document.getElementById('closeIcon');

            const isOpen = menu.classList.toggle('active');
            overlay.classList.toggle('active');

            hamburger.style.display = isOpen ? 'none' : 'block';
            close.style.display = isOpen ? 'block' : 'none';
        }
    </script>

    <script src="{{ asset('js/echarts.min.js') }}"></script>
    @stack('scripts')

    <script>
        // Register Service Worker for PWA
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/web_receiving/public/sw.js')
                .then(function(reg) {
                    console.log('SW registered');
                })
                .catch(function(err) {
                    console.log('SW registration failed:', err);
                });
        }
    </script>
</body>

</html>
