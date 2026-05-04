<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - Partner Panel</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --partner-primary: #F25C3B;
            --partner-primary-hover: #E04A2A;
            --partner-dark: #1a1a1a;
            --partner-bg: #f4f6f8;
            --partner-white: #ffffff;
            --partner-text-gray: #666666;
            --shadow: 0 4px 15px rgba(0,0,0,0.05);
            --transition: all 0.3s ease;
        }

        * { box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: var(--partner-bg); margin: 0; padding: 0; }
        
        /* Navbar */
        .navbar {
            background: var(--partner-dark);
            color: var(--partner-white);
            padding: 0 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .nav-brand { font-size: 22px; font-weight: 700; display: flex; align-items: center; gap: 10px; color: var(--partner-white); text-decoration: none; }
        .nav-brand span { color: var(--partner-primary); }

        .nav-links { display: flex; gap: 30px; align-items: center; }
        .nav-link { 
            color: rgba(255,255,255,0.7); 
            text-decoration: none; 
            font-size: 14px; 
            font-weight: 500; 
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .nav-link:hover, .nav-link.active { color: var(--partner-white); }
        .nav-link.active { color: var(--partner-primary); }

        /* Bottom Nav (Mobile Only) */
        .bottom-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--partner-dark);
            height: 65px;
            z-index: 2000;
            justify-content: space-around;
            align-items: center;
            padding: 0 10px;
            box-shadow: 0 -2px 15px rgba(0,0,0,0.2);
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .bottom-nav-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            font-size: 10px;
            gap: 4px;
            transition: var(--transition);
        }

        .bottom-nav-link svg { width: 22px; height: 22px; }
        .bottom-nav-link.active { color: var(--partner-primary); }

        .nav-right { display: flex; align-items: center; gap: 20px; }
        .btn-end-shift { 
            background: transparent; 
            border: 1.5px solid var(--partner-white); 
            color: var(--partner-white); 
            padding: 8px 20px; 
            border-radius: 50px; 
            cursor: pointer; 
            font-weight: 600; 
            transition: var(--transition);
            font-size: 13px;
        }
        .btn-end-shift:hover { background: var(--partner-white); color: var(--partner-dark); }

        .container { max-width: 1100px; margin: 40px auto; padding: 0 20px; padding-bottom: 40px; }
        
        @media (max-width: 768px) {
            .navbar { padding: 0 20px; }
            .nav-links { display: none; }
            .bottom-nav { display: flex; }
            .container { margin-top: 20px; padding-bottom: 100px; }
            .nav-brand { font-size: 18px; }
            .btn-end-shift { padding: 6px 12px; font-size: 11px; }
        }
    </style>
    @yield('styles')
</head>
<body>
    <nav class="navbar">
        <a href="{{ route('delivery.dashboard') }}" class="nav-brand">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--partner-primary);"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
            E-<span>Shop</span>
        </a>

        <div class="nav-links">
            <a href="{{ route('delivery.dashboard') }}" class="nav-link {{ request()->routeIs('delivery.dashboard') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                Dashboard
            </a>
            <a href="{{ route('delivery.work') }}" class="nav-link {{ request()->routeIs('delivery.work') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                Work
            </a>
            <a href="{{ route('delivery.stores') }}" class="nav-link {{ request()->routeIs('delivery.stores') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                Apply for Stores
            </a>
            <a href="{{ route('delivery.history') }}" class="nav-link {{ request()->routeIs('delivery.history') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                History
            </a>
        </div>

        <div class="nav-right">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-end-shift">END SHIFT</button>
            </form>
        </div>
    </nav>

    {{-- Bottom Navigation for Mobile --}}
    <div class="bottom-nav">
        <a href="{{ route('delivery.dashboard') }}" class="bottom-nav-link {{ request()->routeIs('delivery.dashboard') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('delivery.work') }}" class="bottom-nav-link {{ request()->routeIs('delivery.work') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
            <span>Work</span>
        </a>
        <a href="{{ route('delivery.stores') }}" class="bottom-nav-link {{ request()->routeIs('delivery.stores') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
            <span>Stores</span>
        </a>
        <a href="{{ route('delivery.history') }}" class="bottom-nav-link {{ request()->routeIs('delivery.history') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            <span>History</span>
        </a>
    </div>

    <div class="container">
        @yield('content')
    </div>

    @yield('scripts')
</body>
</html>
