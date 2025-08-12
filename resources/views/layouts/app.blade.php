<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'MirProp') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- HTMX -->
    <script src="https://unpkg.com/htmx.org@1.9.10"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        <!-- Navigation -->
        <nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <a href="{{ route('dashboard') }}">
                                <h2 class="text-xl font-bold text-gray-800">MirProp</h2>
                            </a>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                                Dashboard
                            </x-nav-link>
                            <x-nav-link :href="route('properties.index')" :active="request()->routeIs('properties.*')">
                                Properties
                            </x-nav-link>
                            <x-nav-link :href="route('leases.index')" :active="request()->routeIs('leases.*')">
                                Leases
                            </x-nav-link>
                            <x-nav-link :href="route('payments.index')" :active="request()->routeIs('payments.*')">
                                Payments
                            </x-nav-link>
                            <x-nav-link :href="route('maintenance-tickets.index')" :active="request()->routeIs('maintenance-tickets.*')">
                                Maintenance
                            </x-nav-link>
                            <x-nav-link :href="route('contacts.index')" :active="request()->routeIs('contacts.*', 'vendors.*')">
                                Contacts
                            </x-nav-link>
                        </div>
                    </div>

                    <!-- Settings Dropdown -->
                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <!-- Organization Switcher -->
                        @if(auth()->user()->organizations->count() > 1)
                            <div class="mr-3">
                                <x-dropdown align="right" width="48">
                                    <x-slot name="trigger">
                                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                            <div>{{ auth()->user()->currentOrganization->name }}</div>
                                            <div class="ml-1">
                                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </button>
                                    </x-slot>

                                    <x-slot name="content">
                                        @foreach(auth()->user()->organizations as $org)
                                            <form method="POST" action="{{ route('organizations.switch', $org) }}">
                                                @csrf
                                                <x-dropdown-link :href="route('organizations.switch', $org)" 
                                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                                    {{ $org->name }}
                                                    @if($org->id === auth()->user()->current_organization_id)
                                                        <span class="ml-2 text-xs text-green-600">✓</span>
                                                    @endif
                                                </x-dropdown-link>
                                            </form>
                                        @endforeach
                                    </x-slot>
                                </x-dropdown>
                            </div>
                        @endif

                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                    <div>{{ Auth::user()->name }}</div>
                                    <div class="ml-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('profile.edit')">
                                    {{ __('Profile') }}
                                </x-dropdown-link>

                                <!-- Authentication -->
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link :href="route('logout')"
                                            onclick="event.preventDefault();
                                                        this.closest('form').submit();">
                                        {{ __('Log Out') }}
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>

                    <!-- Hamburger -->
                    <div class="-mr-2 flex items-center sm:hidden">
                        <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Responsive Navigation Menu -->
            <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
                <div class="pt-2 pb-3 space-y-1">
                    <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        Dashboard
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('properties.index')" :active="request()->routeIs('properties.*')">
                        Properties
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('leases.index')" :active="request()->routeIs('leases.*')">
                        Leases
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('payments.index')" :active="request()->routeIs('payments.*')">
                        Payments
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('maintenance-tickets.index')" :active="request()->routeIs('maintenance-tickets.*')">
                        Maintenance
                    </x-responsive-nav-link>
                </div>

                <!-- Responsive Settings Options -->
                <div class="pt-4 pb-1 border-t border-gray-200">
                    <div class="px-4">
                        <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                        <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                    </div>

                    <div class="mt-3 space-y-1">
                        <x-responsive-nav-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-responsive-nav-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-responsive-nav-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-responsive-nav-link>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>

    <!-- Toast Notifications -->
    <div x-data="{ 
            toastMessage: '',
            toastType: 'info',
            showToast: false 
         }"
         @toast.window="
            toastMessage = $event.detail.message || $event.detail;
            toastType = $event.detail.type || 'info';
            showToast = true;
            setTimeout(() => showToast = false, 3000);
         "
         class="fixed top-4 right-4 z-50">
        <div x-show="showToast"
             x-transition:enter="transform ease-out duration-300 transition"
             x-transition:enter-start="translate-y-2 opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="rounded-md shadow-lg p-4"
             :class="{
                'bg-green-50 border border-green-200': toastType === 'success',
                'bg-red-50 border border-red-200': toastType === 'error',
                'bg-blue-50 border border-blue-200': toastType === 'info',
                'bg-yellow-50 border border-yellow-200': toastType === 'warning'
             }">
            <p class="text-sm font-medium"
               :class="{
                  'text-green-800': toastType === 'success',
                  'text-red-800': toastType === 'error',
                  'text-blue-800': toastType === 'info',
                  'text-yellow-800': toastType === 'warning'
               }"
               x-text="toastMessage"></p>
        </div>
    </div>

    <!-- HTMX Configuration -->
    <script>
        // Configure HTMX
        document.body.addEventListener('htmx:configRequest', (event) => {
            event.detail.headers['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        });

        // Handle HTMX response headers for toasts
        document.body.addEventListener('htmx:afterRequest', (event) => {
            const toast = event.detail.xhr.getResponseHeader('X-Toast');
            if (toast) {
                window.dispatchEvent(new CustomEvent('toast', { 
                    detail: JSON.parse(toast) 
                }));
            }
        });
    </script>
</body>
</html>