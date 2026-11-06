<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Sign In — {{ config('app.name', 'Fahd IMS') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full font-sans antialiased text-slate-900 bg-slate-50 flex flex-col justify-center py-10 px-4 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <!-- Brand Identity -->
        <div class="flex flex-col items-center text-center">
            <div class="w-12 h-12 rounded-xl bg-zinc-950 border border-zinc-800 flex items-center justify-center text-white shadow-sm">
                <i data-lucide="boxes" class="w-6 h-6 text-slate-100"></i>
            </div>
            <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-900">
                Fahd IMS
            </h1>
            <p class="mt-1 text-sm text-slate-600">
                Enterprise Inventory Management &amp; Stock Control System
            </p>
        </div>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-6 shadow-xs rounded-2xl border border-slate-200/90 sm:px-10">
            <div class="mb-6">
                <h2 class="text-base font-semibold text-slate-900">Masuk ke Panel Operasional</h2>
                <p class="mt-1 text-xs text-slate-500">
                    Gunakan akun terdaftar untuk mengakses modul gudang dan inventaris.
                </p>
            </div>

            <!-- Logout / Session Status Banner -->
            @if (session('status'))
                <div class="mb-5 rounded-xl bg-emerald-50 border border-emerald-200/80 p-3.5 flex items-start gap-3" role="status">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>
                    <p class="text-xs font-medium text-emerald-800">
                        {{ session('status') }}
                    </p>
                </div>
            @endif

            <!-- Validation / Auth Failed Alert -->
            @if ($errors->any())
                <div class="mb-5 rounded-xl bg-red-50 border border-red-200/80 p-3.5 flex items-start gap-3" role="alert">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 shrink-0 mt-0.5"></i>
                    <div class="text-xs text-red-800 space-y-1">
                        <p class="font-semibold">Autentikasi Gagal</p>
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('login.submit') }}" class="space-y-5" novalidate>
                @csrf

                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Alamat Email
                    </label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </span>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="email"
                            placeholder="nama@fahdims.test"
                            @class([
                                'block w-full rounded-xl pl-10 pr-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 bg-white border transition-colors focus:outline-none focus:ring-2',
                                'border-red-300 focus:border-red-500 focus:ring-red-500/20' => $errors->has('email'),
                                'border-slate-300 focus:border-zinc-900 focus:ring-zinc-900/10' => ! $errors->has('email'),
                            ])
                        >
                    </div>
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Kata Sandi
                    </label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </span>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                            @class([
                                'block w-full rounded-xl pl-10 pr-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 bg-white border transition-colors focus:outline-none focus:ring-2',
                                'border-red-300 focus:border-red-500 focus:ring-red-500/20' => $errors->has('password'),
                                'border-slate-300 focus:border-zinc-900 focus:ring-zinc-900/10' => ! $errors->has('password'),
                            ])
                        >
                    </div>
                </div>

                <!-- Remember Session Checkbox -->
                <div class="flex items-center justify-between">
                    <label for="remember" class="inline-flex items-center gap-2 cursor-pointer select-none">
                        <input
                            id="remember"
                            name="remember"
                            type="checkbox"
                            value="1"
                            @checked(old('remember'))
                            class="w-4 h-4 rounded border-slate-300 text-zinc-900 focus:ring-zinc-900"
                        >
                        <span class="text-xs font-medium text-slate-600">Ingat sesi perangkat ini</span>
                    </label>
                    <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-400">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                        <span>CSRF Protected</span>
                    </span>
                </div>

                <!-- Submit Button -->
                <div class="pt-1">
                    <button
                        type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-zinc-950 px-4 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-950 focus:ring-offset-2 transition-colors cursor-pointer"
                    >
                        <span>Masuk ke Dashboard</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </form>

            <!-- Demo Accounts Quick-Fill Section (Local Development / Portfolio Evaluation) -->
            <div class="mt-7 pt-6 border-t border-slate-100">
                <div class="flex items-center justify-between mb-3">
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                        <i data-lucide="key-round" class="w-3.5 h-3.5 text-slate-400"></i>
                        <span>Akun Demo Seeder (Klik untuk Isi Otomatis)</span>
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <button
                        type="button"
                        data-demo-email="admin@fahdims.test"
                        data-demo-password="password"
                        class="group text-left p-2.5 rounded-xl border border-slate-200 hover:border-zinc-900 hover:bg-slate-50/80 transition-colors cursor-pointer"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-slate-900">Administrator</span>
                            <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-zinc-900 text-white">admin</span>
                        </div>
                        <p class="mt-1 text-[11px] text-slate-500 truncate">admin@fahdims.test</p>
                    </button>

                    <button
                        type="button"
                        data-demo-email="staff@fahdims.test"
                        data-demo-password="password"
                        class="group text-left p-2.5 rounded-xl border border-slate-200 hover:border-zinc-900 hover:bg-slate-50/80 transition-colors cursor-pointer"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-slate-900">Warehouse Staff</span>
                            <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-slate-200 text-slate-700">staff</span>
                        </div>
                        <p class="mt-1 text-[11px] text-slate-500 truncate">staff@fahdims.test</p>
                    </button>
                </div>
            </div>
        </div>

        <p class="mt-6 text-center text-xs text-slate-400">
            Fahd IMS &bull; Role-Based Inventory Control &bull; Laravel {{ app()->version() }}
        </p>
    </div>
</body>
</html>

