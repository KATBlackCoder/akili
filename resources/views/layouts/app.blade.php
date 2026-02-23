<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="fantasy" id="html-root">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }} — Akili</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        // Restore saved theme before first paint to avoid flash
        (function () {
            const saved = localStorage.getItem('akili-theme') || 'fantasy';
            document.documentElement.setAttribute('data-theme', saved);
        })();

        document.addEventListener('htmx:configRequest', function(evt) {
            evt.detail.headers['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        });

        function applyThemeIcons(theme) {
            const isDark = theme === 'luxury';
            // Navbar (mobile)
            const sun = document.getElementById('theme-icon-sun');
            const moon = document.getElementById('theme-icon-moon');
            if (sun) sun.classList.toggle('hidden', !isDark);
            if (moon) moon.classList.toggle('hidden', isDark);
            // Sidebar (desktop)
            const sunSidebar = document.getElementById('theme-icon-sun-sidebar');
            const moonSidebar = document.getElementById('theme-icon-moon-sidebar');
            if (sunSidebar) sunSidebar.classList.toggle('hidden', !isDark);
            if (moonSidebar) moonSidebar.classList.toggle('hidden', isDark);
            const label = document.getElementById('theme-label-sidebar');
            if (label) label.textContent = isDark ? 'Mode clair' : 'Mode sombre';
        }

        function toggleTheme() {
            const html = document.getElementById('html-root');
            const current = html.getAttribute('data-theme');
            const next = current === 'luxury' ? 'fantasy' : 'luxury';
            html.setAttribute('data-theme', next);
            localStorage.setItem('akili-theme', next);
            applyThemeIcons(next);
        }

        document.addEventListener('DOMContentLoaded', function () {
            const saved = localStorage.getItem('akili-theme') || 'fantasy';
            applyThemeIcons(saved);
        });
    </script>
</head>
<body class="font-sans antialiased bg-base-200 min-h-screen">

{{-- Layout Drawer (sidebar desktop) --}}
<div class="drawer lg:drawer-open">
    <input id="main-drawer" type="checkbox" class="drawer-toggle" />

    {{-- Page Content --}}
    <div class="drawer-content flex flex-col min-h-screen">

        {{-- Top Navbar (mobile) --}}
        <header class="lg:hidden sticky top-0 z-30 flex items-center h-14 px-4 bg-base-100 border-b border-base-200 gap-2">
            {{-- Logo --}}
            <span class="flex-1 text-lg font-bold text-primary">Akili</span>

            {{-- Theme toggle --}}
            <button onclick="toggleTheme()" class="btn btn-ghost btn-sm btn-square" aria-label="Thème">
                <svg id="theme-icon-moon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
                <svg id="theme-icon-sun" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </button>

            {{-- Notifications --}}
            <x-notification-bell />

            {{-- Avatar + menu --}}
            <div class="dropdown dropdown-end">
                <div tabindex="0" role="button" class="btn btn-ghost btn-sm btn-square">
                    <div class="bg-primary text-primary-content rounded-lg w-7 h-7 flex items-center justify-center text-xs font-bold">
                        {{ substr(auth()->user()->firstname, 0, 1) }}{{ substr(auth()->user()->lastname, 0, 1) }}
                    </div>
                </div>
                <ul tabindex="0" class="mt-2 z-50 p-1.5 shadow-lg menu menu-sm dropdown-content bg-base-100 border border-base-200 rounded-xl w-52">
                    <li class="menu-title px-3 py-1">
                        <span class="text-xs font-medium">{{ auth()->user()->full_name }}</span>
                    </li>
                    <li><a href="{{ route('employees.show', auth()->user()) }}">Mon profil</a></li>
                    <div class="divider my-0.5"></div>
                    <li>
                        <button type="button" class="text-error" onclick="document.getElementById('sidebar-logout-form').submit()">
                            Déconnexion
                        </button>
                    </li>
                </ul>
            </div>
        </header>

        {{-- Main Content --}}
        <main class="flex-1 p-4 lg:p-6 pb-20 lg:pb-6">
            @if (session('success'))
                <div class="alert alert-success mb-4" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-error mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{ $slot }}
        </main>

        {{-- Bottom Navigation (mobile only) --}}
        <nav class="fixed bottom-0 left-0 right-0 lg:hidden z-20 bg-base-100 border-t border-base-200 flex h-16">
            @php $unread = auth()->user()->unreadNotifications()->count(); @endphp

            <a href="{{ route('dashboard') }}"
               class="flex-1 flex flex-col items-center justify-center gap-0.5 text-xs {{ request()->routeIs('dashboard') ? 'text-primary' : 'text-base-content/50' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>Accueil</span>
            </a>

            <a href="{{ route('assignments.index') }}"
               class="flex-1 flex flex-col items-center justify-center gap-0.5 text-xs {{ request()->routeIs('assignments.*', 'submissions.*') ? 'text-primary' : 'text-base-content/50' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                <span>Questionnaires</span>
            </a>

            @if(!auth()->user()->hasRole('employee'))
            <a href="{{ route('employees.index') }}"
               class="flex-1 flex flex-col items-center justify-center gap-0.5 text-xs {{ request()->routeIs('employees.*') ? 'text-primary' : 'text-base-content/50' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Employés</span>
            </a>
            @endif

            <a href="{{ route('notifications.index') }}"
               class="flex-1 flex flex-col items-center justify-center gap-0.5 text-xs {{ request()->routeIs('notifications.*') ? 'text-primary' : 'text-base-content/50' }}">
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    @if($unread > 0)
                    <span class="absolute -top-1 -right-1 bg-primary text-primary-content text-[10px] font-bold rounded-full min-w-[16px] h-4 flex items-center justify-center px-0.5">
                        {{ $unread > 99 ? '99+' : $unread }}
                    </span>
                    @endif
                </div>
                <span>Notifications</span>
            </a>
        </nav>
    </div>

    {{-- Sidebar --}}
    <div class="drawer-side z-40">
        <label for="main-drawer" class="drawer-overlay"></label>
        <aside class="min-h-screen w-72 bg-base-100 flex flex-col shadow-xl">

            {{-- Logo --}}
            <div class="p-4 border-b border-base-200">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center">
                        <span class="text-primary-content font-bold text-lg">A</span>
                    </div>
                    <div>
                        <div class="font-bold text-lg leading-none">Akili</div>
                        <div class="text-xs text-base-content/50">{{ auth()->user()->company->name }}</div>
                    </div>
                </a>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 p-4 overflow-y-auto">
                <ul class="menu menu-md gap-1">
                    <li>
                        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                            Tableau de bord
                        </a>
                    </li>

                    <li class="menu-title mt-2">Questionnaires</li>

                    @if(!auth()->user()->hasRole('employee'))
                    <li>
                        <a href="{{ route('forms.index') }}" class="{{ request()->routeIs('forms.*') ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            Mes formulaires
                        </a>
                    </li>
                    @endif

                    <li>
                        <a href="{{ route('assignments.index') }}" class="{{ request()->routeIs('assignments.*') ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                            {{ auth()->user()->hasRole('employee') ? 'Mes questionnaires' : 'Assignations' }}
                        </a>
                    </li>

                    <li class="menu-title mt-2">GRH</li>

                    @if(!auth()->user()->hasRole('employee'))
                    <li>
                        <a href="{{ route('employees.index') }}" class="{{ request()->routeIs('employees.*') ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            Employés
                        </a>
                    </li>
                    @endif
                </ul>
            </nav>

            {{-- Theme toggle (desktop) --}}
            <div class="px-3 pb-2">
                <button onclick="toggleTheme()"
                        class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-sm text-base-content/50 hover:bg-base-200 hover:text-base-content transition-colors">
                    <svg id="theme-icon-moon-sidebar" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    <svg id="theme-icon-sun-sidebar" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span id="theme-label-sidebar">Mode sombre</span>
                </button>
            </div>

            {{-- User section --}}
            <div class="p-3 border-t border-base-200">
                <div class="dropdown dropdown-top w-full">
                    <div tabindex="0" role="button"
                         class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-base-200 transition-colors cursor-pointer w-full group">
                        <div class="bg-primary text-primary-content rounded-xl w-9 h-9 flex items-center justify-center font-semibold text-sm flex-shrink-0">
                            <span>{{ substr(auth()->user()->firstname, 0, 1) }}{{ substr(auth()->user()->lastname, 0, 1) }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-sm leading-tight truncate">{{ auth()->user()->full_name }}</div>
                            <div class="text-xs text-base-content/40 truncate capitalize">{{ str_replace('-', ' ', auth()->user()->getRoleNames()->first() ?? '') }}</div>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-base-content/30 group-hover:text-base-content/60 transition-colors flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                        </svg>
                    </div>
                    <ul tabindex="0" class="dropdown-content menu p-1.5 shadow-lg bg-base-100 border border-base-200 rounded-xl w-56 z-50 mb-1">
                        <li class="menu-title px-3 py-1.5">
                            <span class="text-xs font-medium text-base-content/50">{{ auth()->user()->full_name }}</span>
                        </li>
                        <li>
                            <a href="{{ route('employees.show', auth()->user()) }}" class="gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Mon profil
                            </a>
                        </li>
                        <div class="divider my-0.5"></div>
                        <li>
                            <button type="button" class="text-error gap-3 w-full"
                                    onclick="document.getElementById('sidebar-logout-form').submit()">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                Déconnexion
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

            <form id="sidebar-logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
                @csrf
            </form>
        </aside>
    </div>
</div>

</body>
</html>
