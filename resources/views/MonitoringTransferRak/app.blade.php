<!DOCTYPE html>
<html lang="id" style="background:#0b1220">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="base-url" content="{{ url('/') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Forklift Monitor')</title>
    {{-- Critical CSS: dark background sebelum apapun load --}}
    <style>
        html,
        body {
            background: #0b1220 !important;
            margin: 0
        }
    </style>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #0f1419 0%, #1a1f2e 50%, #16213e 100%);
            background-attachment: fixed;
            color: #e0e0e0;
            padding-bottom: 60px;
            min-height: 100vh;
            transition: padding-bottom 0.3s ease;
        }

        body.navbar-hidden {
            padding-bottom: 0;
        }

        /* ===== NAVBAR STYLING (COMPACT) ===== */
        .navbar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(180deg, rgba(15, 20, 25, 0.98) 0%, rgba(26, 31, 46, 0.99) 100%);
            backdrop-filter: blur(10px);
            border-top: 1px solid rgba(100, 200, 255, 0.2);
            box-shadow: 0 -2px 20px rgba(0, 0, 0, 0.5), 0 -2px 8px rgba(100, 200, 255, 0.1);
            z-index: 100;
            height: 60px;
            overflow: hidden;
            transition: transform 0.3s ease;
            transform: translateY(0);
        }

        .navbar.hide {
            transform: translateY(100%);
        }

        .nav-container {
            display: flex;
            justify-content: space-around;
            align-items: center;
            height: 60px;
            max-width: 100%;
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex: 1;
            height: 100%;
            text-decoration: none;
            color: #8899aa;
            font-size: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            gap: 2px;
            padding: 8px 4px;
        }

        .nav-item:hover {
            color: #64c8ff;
        }

        .nav-item.active {
            color: #64c8ff;
            text-shadow: 0 0 10px rgba(100, 200, 255, 0.5);
        }

        .nav-item.active::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, #64c8ff, transparent);
            box-shadow: 0 0 10px #64c8ff, 0 0 20px rgba(100, 200, 255, 0.5);
            border-radius: 2px;
        }

        .svg-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            transition: all 0.3s ease;
        }

        .nav-item:hover .svg-icon {
            transform: scale(1.15);
            filter: drop-shadow(0 0 8px rgba(100, 200, 255, 0.6));
        }

        .nav-item.active .svg-icon {
            filter: drop-shadow(0 0 10px rgba(100, 200, 255, 0.8));
        }

        .svg-icon svg {
            width: 100%;
            height: 100%;
            stroke-width: 1.5;
            color: currentColor;
        }

        /* ===== SWIPE INDICATOR ===== */
        .swipe-indicator {
            position: fixed;
            bottom: 62px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(100, 200, 255, 0.2);
            border: 1px solid rgba(100, 200, 255, 0.4);
            color: #64c8ff;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            opacity: 0;
            animation: fadeInOut 3s ease-in-out;
            pointer-events: none;
            z-index: 102;
        }

        @keyframes fadeInOut {
            0% {
                opacity: 0;
                transform: translateX(-50%) translateY(10px);
            }

            20% {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }

            80% {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }

            100% {
                opacity: 0;
                transform: translateX(-50%) translateY(-10px);
            }
        }

        /* ===== TOP BAR (Mobile Header) ===== */
        .topbar {
            background: linear-gradient(180deg, rgba(26, 31, 46, 0.98) 0%, rgba(15, 20, 25, 0.95) 100%);
            padding: 12px 16px;
            border-bottom: 1px solid rgba(100, 200, 255, 0.15);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 99;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .topbar-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 18px;
            font-weight: 600;
            color: #64c8ff;
            text-shadow: 0 0 10px rgba(100, 200, 255, 0.3);
        }

        .topbar .svg-icon {
            width: 24px;
            height: 24px;
            color: #64c8ff;
            filter: drop-shadow(0 0 5px rgba(100, 200, 255, 0.5));
        }

        .topbar-time {
            font-size: 12px;
            color: #666;
            font-family: 'Courier New', monospace;
            letter-spacing: 1px;
        }

        /* ===== MAIN CONTAINER ===== */
        .container {
            padding: 16px;
            max-width: 100%;
            margin: 0 auto;
        }

        /* ===== ALERTS ===== */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 14px;
            animation: slideIn 0.3s ease;
            backdrop-filter: blur(5px);
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .alert-success {
            background: rgba(76, 175, 80, 0.15);
            color: #4caf50;
            border: 1px solid rgba(76, 175, 80, 0.3);
            box-shadow: 0 0 15px rgba(76, 175, 80, 0.2);
        }

        .alert-error {
            background: rgba(244, 67, 54, 0.15);
            color: #ff6b6b;
            border: 1px solid rgba(244, 67, 54, 0.3);
            box-shadow: 0 0 15px rgba(244, 67, 54, 0.2);
        }

        .success-pop {
            animation: popOut 0.5s ease forwards;
        }

        @keyframes popOut {
            0% {
                opacity: 1;
                transform: scale(1);
            }

            80% {
                opacity: 1;
                transform: scale(1);
            }

            100% {
                opacity: 0;
                transform: scale(0.9);
            }
        }

        /* ===== SCROLLBAR STYLING ===== */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(100, 200, 255, 0.05);
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(100, 200, 255, 0.3);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 200, 255, 0.5);
        }

        /* ===== RESPONSIVE ===== */
        @media (min-width: 768px) {
            .navbar {
                top: 0;
                bottom: auto;
                border-top: none;
                border-bottom: 1px solid rgba(100, 200, 255, 0.2);
                box-shadow: 0 2px 20px rgba(0, 0, 0, 0.5), 0 2px 8px rgba(100, 200, 255, 0.1);
                transform: translateY(0) !important;
            }

            body {
                padding-bottom: 0;
                padding-top: 60px;
            }

            .nav-item {
                font-size: 12px;
                gap: 4px;
            }

            .nav-item.active::before {
                top: auto;
                bottom: 0;
            }

            .swipe-indicator {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .nav-item {
                font-size: 9px;
                padding: 6px 2px;
            }

            .svg-icon {
                width: 18px;
                height: 18px;
            }
        }
    </style>
    @stack('styles')
</head>

<body>

    {{-- Bottom Navigation --}}
    <nav class="navbar" id="navbar">
        <div class="nav-container">

            {{-- DASHBOARD --}}
            <a href="{{ route('transfer.dashboard') }}"
                class="nav-item {{ request()->routeIs('transfer.dashboard') ? 'active' : '' }}">
                <span class="svg-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <rect x="3" y="12" width="4" height="9" rx="1" />
                        <rect x="10" y="8" width="4" height="13" rx="1" />
                        <rect x="17" y="4" width="4" height="17" rx="1" />
                    </svg>
                </span>
                <span>Dashboard</span>
            </a>

            {{-- INPUT --}}
            <a href="{{ route('transfer.index') }}"
                class="nav-item {{ request()->routeIs('transfer.index') ? 'active' : '' }}">
                <span class="svg-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="9" />
                        <line x1="12" y1="8" x2="12" y2="16" />
                        <line x1="8" y1="12" x2="16" y2="12" />
                    </svg>
                </span>
                <span>Input</span>
            </a>

            {{-- PROFILE --}}
            <a href="{{ route('karyawan.index') }}" class="nav-item">
                <span class="svg-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                </span>
                <span>Karyawan</span>
            </a>

            {{-- LAPORAN --}}
            <a href="{{ route('transfer.laporan') }}"
                class="nav-item {{ request()->routeIs('transfer.laporan*') ? 'active' : '' }}">
                <span class="svg-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M3 3v18h18" />
                        <path d="M7 12l3-3 4 4 6-6" />
                    </svg>
                </span>
                <span>Laporan</span>
            </a>

            {{-- BACK TO MENU --}}
            <a href="{{ route('pilihmenu.index') }}" class="nav-item {{ request()->routeIs('menu') ? 'active' : '' }}">
                <span class="svg-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M19 12H5" />
                        <path d="M12 19l-7-7 7-7" />
                    </svg>
                </span>
                <span>Menu</span>
            </a>

        </div>
    </nav>

    {{-- Main Content --}}
    <div class="container">
        @if (session('success'))
            <div class="alert alert-success success-pop">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @yield('content')
    </div>

    <script>
        // ===== SCROLL DETECTION (FIXED) =====
        let lastScrollTop = 0;

        window.addEventListener('scroll', () => {
            const scrollTop = window.pageYOffset;

            if (scrollTop > lastScrollTop + 20) {
                navbar.classList.add('hide');
                body.classList.add('navbar-hidden');
            } else if (scrollTop < lastScrollTop - 20) {
                navbar.classList.remove('hide');
                body.classList.remove('navbar-hidden');
            }

            lastScrollTop = scrollTop;
        }, {
            passive: true
        });
    </script>

    @yield('scripts')
    @stack('scripts')
</body>

</html>
