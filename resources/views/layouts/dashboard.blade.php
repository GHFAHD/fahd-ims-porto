@props([
    'title' => 'Dashboard',
    'breadcrumb' => 'Overview',
])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} — {{ config('app.name', 'Fahd IMS') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-900 bg-slate-50 selection:bg-slate-900 selection:text-white">
    @php
        /** @var \App\Models\User|null $currentUser */
        $currentUser = auth()->user();
    @endphp

    <div class="min-h-full flex">
        <!-- Mobile Sidebar Backdrop -->
        <div
            id="ims-sidebar-backdrop"
            class="fixed inset-0 z-30 bg-slate-950/50 backdrop-blur-xs hidden lg:hidden transition-opacity"
            aria-hidden="true"
        ></div>

        <!-- Sidebar -->
        <aside
            id="ims-sidebar"
            class="fixed inset-y-0 left-0 z-40 w-64 flex flex-col bg-zinc-950 text-zinc-300 border-r border-zinc-800/80 transform -translate-x-full lg:translate-x-0 lg:static lg:inset-auto lg:shrink-0 transition-transform duration-200 ease-in-out"
            aria-label="Sidebar Navigation"
        >
            <!-- Brand Header -->
            <div class="h-16 px-5 flex items-center justify-between border-b border-zinc-800/80">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                    <span class="w-9 h-9 rounded-lg bg-zinc-900 border border-zinc-700/80 flex items-center justify-center text-white shadow-xs group-hover:border-zinc-500 transition-colors">
                        <i data-lucide="boxes" class="w-5 h-5 text-slate-100"></i>
                    </span>
                    <div class="flex flex-col">
                        <span class="text-sm font-semibold tracking-tight text-white leading-none">Fahd IMS</span>
                        <span class="text-[11px] font-medium text-zinc-400 mt-1 leading-none">Inventory Control</span>
                    </div>
                </a>
                <button
                    type="button"
                    data-sidebar-toggle
                    class="lg:hidden p-1.5 rounded-md text-zinc-400 hover:text-white hover:bg-zinc-800/60"
                    aria-label="Close sidebar"
                >
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Navigation Links -->
            <div class="flex-1 overflow-y-auto py-5 px-3 space-y-6">
                <div>
                    <p class="px-3 mb-2 text-[11px] font-semibold uppercase tracking-wider text-zinc-500">
                        Core Operations
                    </p>
                    <nav class="space-y-1">
                        @php
                            $navItems = [
                                [
                                    'label' => 'Dashboard',
                                    'icon' => 'layout-dashboard',
                                    'href' => route('dashboard'),
                                    'active' => request()->routeIs('dashboard'),
                                    'available' => true,
                                ],
                                [
                                    'label' => 'Products',
                                    'icon' => 'package',
                                    'href' => route('products.index'),
                                    'active' => request()->routeIs('products.*'),
                                    'available' => true,
                                ],
                                [
                                    'label' => 'Stock In/Out',
                                    'icon' => 'arrow-left-right',
                                    'href' => route('transactions.index'),
                                    'active' => request()->routeIs('transactions.*'),
                                    'available' => true,
                                ],
                                [
                                    'label' => 'Reports',
                                    'icon' => 'file-bar-chart',
                                    'href' => route('reports.index'),
                                    'active' => request()->routeIs('reports.*'),
                                    'available' => true,
                                ],
                                [
                                    'label' => 'Settings',
                                    'icon' => 'settings',
                                    'href' => '#',
                                    'active' => false,
                                    'available' => false,
                                ],
                            ];
                        @endphp

                        @foreach ($navItems as $item)
                            <a
                                href="{{ $item['href'] }}"
                                @class([
                                    'group flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors',
                                    'bg-zinc-800/90 text-white shadow-2xs ring-1 ring-white/10' => $item['active'],
                                    'text-zinc-300 hover:text-white hover:bg-zinc-900' => ! $item['active'] && $item['available'],
                                    'text-zinc-500 hover:text-zinc-300 hover:bg-zinc-900/60' => ! $item['active'] && ! $item['available'],
                                ])
                            >
                                <span class="flex items-center gap-3">
                                    <i
                                        data-lucide="{{ $item['icon'] }}"
                                        @class([
                                            'w-4 h-4 shrink-0 transition-colors',
                                            'text-white' => $item['active'],
                                            'text-zinc-400 group-hover:text-zinc-200' => ! $item['active'],
                                        ])
                                    ></i>
                                    <span>{{ $item['label'] }}</span>
                                </span>

                                @if (! $item['available'])
                                    <span class="text-[10px] font-medium px-1.5 py-0.5 rounded bg-zinc-900 text-zinc-500 border border-zinc-800">
                                        Soon
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </nav>
                </div>
            </div>

            <!-- Sidebar Footer Metadata -->
            <div class="p-4 border-t border-zinc-800/80 bg-zinc-950/60">
                <div class="flex items-center gap-3 px-2 py-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 shrink-0"></span>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-zinc-200 truncate">Main Warehouse (WH-01)</p>
                        <p class="text-[11px] text-zinc-500 truncate">System Online &bull; v3.0-master</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Workspace Column -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top Header -->
            <header class="sticky top-0 z-20 h-16 bg-white/95 backdrop-blur-xs border-b border-slate-200 px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-4">
                <!-- Left: Mobile Trigger + Breadcrumb Placeholder -->
                <div class="flex items-center gap-3 min-w-0">
                    <button
                        type="button"
                        data-sidebar-toggle
                        class="lg:hidden p-2 -ml-1 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100"
                        aria-label="Open sidebar"
                    >
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>

                    <nav class="flex items-center gap-2 text-sm min-w-0" aria-label="Breadcrumb">
                        <span class="font-medium text-slate-500 hidden sm:inline">Fahd IMS</span>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400 hidden sm:inline shrink-0"></i>
                        <span class="font-medium text-slate-600 truncate">{{ $title }}</span>
                        @if ($breadcrumb)
                            <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400 shrink-0"></i>
                            <span class="font-semibold text-slate-900 truncate">{{ $breadcrumb }}</span>
                        @endif
                    </nav>
                </div>

                <!-- Right: Warehouse Indicator + Authenticated User Profile & Logout -->
                <div class="flex items-center gap-3">
                    <div class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-100 border border-slate-200/80 text-xs font-medium text-slate-700">
                        <i data-lucide="building-2" class="w-3.5 h-3.5 text-slate-500"></i>
                        <span>Central Distribution</span>
                    </div>

                    <button
                        type="button"
                        class="p-2 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-100 border border-transparent hover:border-slate-200 transition-colors"
                        title="Notifications (Placeholder)"
                    >
                        <i data-lucide="bell" class="w-4 h-4"></i>
                    </button>

                    <div class="h-5 w-px bg-slate-200" aria-hidden="true"></div>

                    <!-- User Profile Display -->
                    <div class="flex items-center gap-2.5 pl-1 pr-1 py-1">
                        <span class="w-8 h-8 rounded-lg bg-zinc-900 text-white text-xs font-semibold flex items-center justify-center shrink-0">
                            {{ $currentUser ? $currentUser->initials() : 'FA' }}
                        </span>
                        <div class="hidden sm:block leading-tight">
                            <div class="flex items-center gap-1.5">
                                <p class="text-xs font-semibold text-slate-800">
                                    {{ $currentUser->name ?? 'Inventory User' }}
                                </p>
                                @if ($currentUser)
                                    <span
                                        @class([
                                            'text-[10px] font-semibold uppercase px-1.5 py-0.5 rounded',
                                            'bg-zinc-900 text-white' => $currentUser->isAdmin(),
                                            'bg-slate-200 text-slate-700' => ! $currentUser->isAdmin(),
                                        ])
                                    >
                                        {{ $currentUser->role }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-[11px] text-slate-500">
                                {{ $currentUser ? $currentUser->email : 'Operations' }}
                            </p>
                        </div>
                    </div>

                    <!-- Logout Form Button -->
                    @if ($currentUser)
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                type="submit"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-medium text-slate-600 hover:text-red-700 hover:bg-red-50 border border-slate-200 hover:border-red-200 transition-colors cursor-pointer"
                                title="Keluar dari sesi"
                            >
                                <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                                <span class="hidden sm:inline">Keluar</span>
                            </button>
                        </form>
                    @endif
                </div>
            </header>

            <!-- Main Content Slot -->
            <main class="flex-1 py-6 px-4 sm:px-6 lg:px-8 max-w-7xl w-full mx-auto">
                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
