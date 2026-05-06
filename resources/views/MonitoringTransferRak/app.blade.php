{{-- resources/views/MonitoringTransferRak/app.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="base-url" content="{{ url('/') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Forklift Monitor')</title>

    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        html { background: #0b1220; }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #0b1220;
            color: #e0e0e0;
            min-height: 100vh;
            padding-bottom: 60px;
        }

        /* ── NAVBAR ── */
        .navbar {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            background: #111827;
            border-top: 1px solid rgba(100, 200, 255, 0.2);
            height: 60px;
            z-index: 100;
            transform: translateY(0);
            transition: transform 0.25s ease;
        }

        .navbar.hide { transform: translateY(100%); }

        .nav-container {
            display: flex;
            height: 100%;
        }

        .nav-item {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 3px;
            text-decoration: none;
            color: #4a5568;
            font-size: 10px;
            font-weight: 500;
            position: relative;
            transition: color 0.2s;
        }

        .nav-item:active { opacity: 0.7; }

        .nav-item.active {
            color: #64c8ff;
        }

        /* Garis hijau atas item aktif */
        .nav-item.active::before {
            content: '';
            position: absolute;
            top: 0;
            left: 20%; right: 20%;
            height: 2px;
            background: #64c8ff;
            border-radius: 0 0 3px 3px;
           box-shadow: 0 0 8px rgba(100, 200, 255, 0.5);
        }

        .nav-item svg {
            width: 20px; height: 20px;
            stroke: currentColor;
            fill: none;
            stroke-width: 1.8;
        }

        /* ── CONTAINER ── */
        .container {
            padding: 16px;
            max-width: 100%;
        }

        /* ── ALERTS ── */
        .alert {
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 14px;
            font-size: 13px;
        }

        .alert-success {
            background: rgba(46, 204, 113, 0.12);
            color: #2ecc71;
            border: 1px solid rgba(46, 204, 113, 0.25);
        }

        .alert-error {
            background: rgba(231, 76, 60, 0.12);
            color: #e74c3c;
            border: 1px solid rgba(231, 76, 60, 0.25);
        }

        /* ── SCROLLBAR ── */
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(46, 204, 113, 0.3); border-radius: 4px; }

        @stack('styles')
    </style>
</head>

<body>

    <nav class="navbar" id="navbar">
        <div class="nav-container">

            <a href="{{ route('transfer.dashboard') }}"
               class="nav-item {{ request()->routeIs('transfer.dashboard') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24"><rect x="3" y="12" width="4" height="9" rx="1"/><rect x="10" y="8" width="4" height="13" rx="1"/><rect x="17" y="4" width="4" height="17" rx="1"/></svg>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('transfer.index') }}"
               class="nav-item {{ request()->routeIs('transfer.index') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                <span>Input</span>
            </a>

            <a href="{{ route('karyawan.index') }}"
             class="nav-item {{ request()->routeIs('karyawan.index') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span>Karyawan</span>
            </a>

            <a href="{{ route('transfer.laporan') }}"
               class="nav-item {{ request()->routeIs('transfer.laporan*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M7 12l3-3 4 4 6-6"/></svg>
                <span>Laporan</span>
            </a>

            <a href="{{ route('pilihmenu.index') }}"
               class="nav-item {{ request()->routeIs('pilihmenu.index') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
                <span>Menu</span>
            </a>

        </div>
    </nav>

    <div class="container">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
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
        // Hide navbar on scroll down, show on scroll up
        let lastY = 0;
        const navbar = document.getElementById('navbar');

        window.addEventListener('scroll', () => {
            const y = window.scrollY;
            navbar.classList.toggle('hide', y > lastY + 10 && y > 60);
            if (y < lastY - 10) navbar.classList.remove('hide');
            lastY = y;
        }, { passive: true });
    </script>

    @yield('scripts')
    @stack('scripts')
</body>
</html>