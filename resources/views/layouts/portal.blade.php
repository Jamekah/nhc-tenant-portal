<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'NHC Tenant Portal') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-gray-100 font-sans antialiased">

    <!-- Header -->
    <header class="sticky top-0 z-50 bg-blue-900 shadow-lg">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <!-- Logo & Brand -->
            <div class="flex items-center space-x-3">
                <img src="{{ asset('images/nhc-logo.png') }}" alt="NHC Logo" class="h-14 w-auto">
                <div>
                    <h1 class="text-lg font-bold leading-tight text-white">NHC Tenant Portal</h1>
                    <p class="text-xs text-blue-200">National Housing Corporation</p>
                </div>
            </div>

            <!-- User Menu (Desktop) -->
            <div class="hidden items-center space-x-4 sm:flex">
                <span class="text-sm text-blue-100">
                    Welcome, <span class="font-semibold text-yellow-400">{{ Auth::user()->name }}</span>
                </span>
                <form method="POST" action="{{ route('portal.logout') }}">
                    @csrf
                    <button type="submit"
                            class="rounded-md bg-blue-800 px-3 py-1.5 text-sm font-medium text-blue-100 transition hover:bg-blue-700 hover:text-white">
                        Logout
                    </button>
                </form>
            </div>

            <!-- Mobile Menu Button -->
            <button id="mobile-menu-btn" class="rounded-md p-2 text-blue-200 hover:bg-blue-800 sm:hidden">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>

        <!-- Mobile User Info -->
        <div id="mobile-menu" class="hidden border-t border-blue-800 bg-blue-900 px-4 py-3 sm:hidden">
            <div class="flex items-center justify-between">
                <span class="text-sm text-blue-100">{{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('portal.logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-yellow-400 hover:text-yellow-300">Logout</button>
                </form>
            </div>
        </div>
    </header>

    <div class="flex min-h-[calc(100vh-4rem)]">

        <!-- Sidebar Navigation (Desktop) -->
        <aside class="hidden w-64 flex-shrink-0 border-r border-gray-200 bg-white shadow-sm sm:block">
            <nav class="space-y-1 px-3 py-4">
                @php
                    $navItems = [
                        ['name' => 'Dashboard',       'route' => 'portal.dashboard',     'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                        ['name' => 'Invoices',        'route' => 'portal.invoices',      'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                        ['name' => 'Payments',        'route' => 'portal.payments',      'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
                        ['name' => 'Make Payment',    'route' => 'portal.make-payment',  'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                        ['name' => 'Support Tickets', 'route' => 'portal.tickets',       'icon' => 'M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z'],
                        ['name' => 'Profile',         'route' => 'portal.profile',       'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                    ];
                @endphp

                @foreach($navItems as $item)
                    @php
                        $isActive = request()->routeIs($item['route']) || request()->routeIs($item['route'] . '.*');
                    @endphp
                    <a href="{{ route($item['route']) }}"
                       class="group flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition
                              {{ $isActive
                                  ? 'bg-blue-50 text-blue-900'
                                  : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="mr-3 h-5 w-5 flex-shrink-0 {{ $isActive ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500' }}"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                        </svg>
                        {{ $item['name'] }}
                    </a>
                @endforeach
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto">
            <div class="mx-auto max-w-7xl px-4 py-6 pb-24 sm:px-6 sm:pb-6 lg:px-8">
                {{ $slot }}
            </div>
        </main>
    </div>

    <!-- Bottom Navigation (Mobile) -->
    <nav class="fixed bottom-0 left-0 right-0 z-50 border-t border-gray-200 bg-white sm:hidden">
        <div class="grid h-16 grid-cols-5">
            @php
                $mobileNavItems = [
                    ['name' => 'Home',     'route' => 'portal.dashboard',    'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                    ['name' => 'Invoices', 'route' => 'portal.invoices',     'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                    ['name' => 'Pay',      'route' => 'portal.make-payment', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                    ['name' => 'Support',  'route' => 'portal.tickets',      'icon' => 'M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z'],
                    ['name' => 'Profile',  'route' => 'portal.profile',      'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                ];
            @endphp

            @foreach($mobileNavItems as $item)
                @php
                    $isMobileActive = request()->routeIs($item['route']) || request()->routeIs($item['route'] . '.*');
                @endphp
                <a href="{{ route($item['route']) }}"
                   class="flex flex-col items-center justify-center text-xs
                          {{ $isMobileActive
                              ? 'text-blue-600'
                              : 'text-gray-500 hover:text-gray-700' }}">
                    <svg class="mb-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                    </svg>
                    {{ $item['name'] }}
                </a>
            @endforeach
        </div>
    </nav>

    <!-- Toast Notification Container -->
    <div id="toast-container"
         x-data="{
            toasts: [],
            addToast(type, message) {
                const id = Date.now();
                this.toasts.push({ id, type, message });
                setTimeout(() => this.removeToast(id), 4000);
            },
            removeToast(id) {
                this.toasts = this.toasts.filter(t => t.id !== id);
            }
         }"
         @toast.window="addToast($event.detail.type, $event.detail.message)"
         class="fixed top-20 right-4 z-[100] space-y-2 sm:right-6">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="true"
                 x-transition:enter="transform ease-out duration-300 transition"
                 x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                 x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="pointer-events-auto w-72 overflow-hidden rounded-lg shadow-lg ring-1 ring-black ring-opacity-5"
                 :class="{
                    'bg-green-50 ring-green-200': toast.type === 'success',
                    'bg-red-50 ring-red-200': toast.type === 'error',
                    'bg-amber-50 ring-amber-200': toast.type === 'warning',
                    'bg-blue-50 ring-blue-200': toast.type === 'info',
                 }">
                <div class="flex items-center gap-2 px-4 py-3">
                    <template x-if="toast.type === 'success'">
                        <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    </template>
                    <template x-if="toast.type === 'error'">
                        <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </template>
                    <template x-if="toast.type === 'warning'">
                        <svg class="h-5 w-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                    </template>
                    <template x-if="toast.type === 'info'">
                        <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                    </template>
                    <p class="text-sm font-medium"
                       :class="{
                          'text-green-800': toast.type === 'success',
                          'text-red-800': toast.type === 'error',
                          'text-amber-800': toast.type === 'warning',
                          'text-blue-800': toast.type === 'info',
                       }"
                       x-text="toast.message"></p>
                    <button @click="removeToast(toast.id)" class="ml-auto text-gray-400 hover:text-gray-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        </template>
    </div>

    @livewireScripts

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-btn')?.addEventListener('click', function () {
            document.getElementById('mobile-menu')?.classList.toggle('hidden');
        });

        // Listen for Livewire toast events
        document.addEventListener('livewire:init', () => {
            Livewire.on('toast', (event) => {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { type: event[0].type, message: event[0].message }
                }));
            });
        });
    </script>
</body>
</html>
