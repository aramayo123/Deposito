<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Depósito') — Sistema</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        dep: {
                            bg: '#0f1117',
                            sidebar: '#151820',
                            card: '#1c2030',
                            border: '#2a3048',
                        }
                    }
                }
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0-rc2/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
</head>
<body class="deposito-app text-gray-200">
<div class="flex min-h-screen">
    <aside id="sidebar" class="deposito-sidebar deposito-transition fixed inset-y-0 left-0 z-40 w-64 -translate-x-full border-r border-dep-border md:relative md:translate-x-0" aria-label="Navegación principal">
        <div class="flex h-14 items-center border-b border-dep-border px-4 text-lg font-semibold tracking-tight text-white">
            <span class="text-blue-400">●</span>
            <span class="ml-2">Depósito</span>
        </div>
        <nav class="space-y-1 p-3 text-sm">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 hover:bg-white/5 deposito-transition {{ request()->routeIs('dashboard') ? 'bg-blue-600/20 text-blue-300' : 'text-gray-300' }}">
                <span>⌂</span> Dashboard
            </a>
            <a href="{{ route('products.index') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 hover:bg-white/5 deposito-transition {{ request()->routeIs('products.*') ? 'bg-blue-600/20 text-blue-300' : 'text-gray-300' }}">
                <span>▦</span> Productos
            </a>
            <a href="{{ route('entries.index') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 hover:bg-white/5 deposito-transition {{ request()->routeIs('entries.*') ? 'bg-blue-600/20 text-blue-300' : 'text-gray-300' }}">
                <span>↓</span> Entradas
            </a>
            <a href="{{ route('exits.index') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 hover:bg-white/5 deposito-transition {{ request()->routeIs('exits.*') ? 'bg-blue-600/20 text-blue-300' : 'text-gray-300' }}">
                <span>↑</span> Salidas
            </a>
            <a href="{{ route('reports.global') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 hover:bg-white/5 deposito-transition {{ request()->routeIs('reports.*') ? 'bg-blue-600/20 text-blue-300' : 'text-gray-300' }}">
                <span>⌕</span> Búsqueda global
            </a>
        </nav>
    </aside>
    <div id="sidebar-backdrop" class="fixed inset-0 z-[35] bg-black/60 opacity-0 pointer-events-none transition-opacity duration-200 md:hidden" aria-hidden="true"></div>
    <div class="flex min-h-screen flex-1 flex-col md:pl-0">
        <header class="sticky top-0 z-30 flex h-14 items-center justify-between border-b border-dep-border bg-dep-bg/95 px-4 backdrop-blur">
            <div class="flex items-center gap-3">
                <button type="button" id="sidebar-toggle" class="relative z-10 rounded p-2 text-gray-300 hover:bg-white/5 touch-manipulation md:hidden" aria-controls="sidebar" aria-expanded="false" aria-label="Abrir menú">☰</button>
                <div>
                    <div class="text-xs uppercase tracking-wider text-gray-500">Sistema de gestión</div>
                    <div class="text-sm font-semibold text-white">@yield('header-title', 'Depósito')</div>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="relative" id="notif-wrap">
                    <button type="button" id="notif-bell" class="relative rounded p-2 text-gray-300 hover:bg-white/5">
                        🔔
                        <span id="notif-badge" class="absolute -right-0.5 -top-0.5 hidden min-w-[1.1rem] rounded-full bg-red-500 px-1 text-center text-[10px] font-bold text-white">0</span>
                    </button>
                    <div id="notif-panel" class="absolute right-0 mt-2 hidden w-80 max-h-96 overflow-auto rounded-lg border border-dep-border bg-dep-card p-2 text-xs shadow-xl">
                        <div class="mb-2 font-semibold text-gray-400">Notificaciones</div>
                        <ul id="notif-list" class="space-y-2"></ul>
                    </div>
                </div>
                <div class="hidden text-right text-xs text-gray-500 sm:block">
                    @stack('breadcrumb')
                </div>
            </div>
        </header>
        <main class="flex-1 overflow-auto p-4 md:p-6">
            @if(session('success'))
                <div class="mb-4 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-2 text-sm text-emerald-200">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-4 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-2 text-sm text-red-200">
                    <ul class="list-inside list-disc">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
<div id="deposito-toast-host"></div>
<script>
    window.__DEPOSITO__ = {
        csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        reverbKey: @json(config('broadcasting.connections.reverb.key')),
        wsHost: @json(env('REVERB_HOST', '127.0.0.1')),
        wsPort: @json((int) env('REVERB_PORT', 8080)),
        wss: @json(env('REVERB_SCHEME', 'http') === 'https'),
    };
</script>
<script src="{{ asset('js/notifications.js') }}"></script>
<script>
(function () {
    var btn = document.getElementById('sidebar-toggle');
    var side = document.getElementById('sidebar');
    var bd = document.getElementById('sidebar-backdrop');
    if (!btn || !side) return;

    function isMobile() {
        return window.matchMedia('(max-width: 767px)').matches;
    }

    function openMenu() {
        side.classList.add('is-open');
        btn.setAttribute('aria-expanded', 'true');
        btn.setAttribute('aria-label', 'Cerrar menú');
        if (bd) {
            bd.classList.remove('pointer-events-none', 'opacity-0');
            bd.classList.add('opacity-100');
            bd.setAttribute('aria-hidden', 'false');
        }
        document.body.style.overflow = 'hidden';
    }

    function closeMenu() {
        side.classList.remove('is-open');
        btn.setAttribute('aria-expanded', 'false');
        btn.setAttribute('aria-label', 'Abrir menú');
        if (bd) {
            bd.classList.add('pointer-events-none', 'opacity-0');
            bd.classList.remove('opacity-100');
            bd.setAttribute('aria-hidden', 'true');
        }
        document.body.style.overflow = '';
    }

    function toggleMenu() {
        if (!isMobile()) return;
        if (side.classList.contains('is-open')) closeMenu();
        else openMenu();
    }

    btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        toggleMenu();
    });

    if (bd) {
        bd.addEventListener('click', function () {
            closeMenu();
        });
    }

    side.querySelectorAll('nav a').forEach(function (a) {
        a.addEventListener('click', function () {
            if (isMobile()) closeMenu();
        });
    });

    window.addEventListener('resize', function () {
        if (!isMobile()) closeMenu();
    });
})();
</script>
@stack('scripts')
</body>
</html>
