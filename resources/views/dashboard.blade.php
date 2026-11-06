@component('layouts.dashboard', ['title' => 'Dashboard', 'breadcrumb' => 'Operational Overview'])
    <!-- Page Heading -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Inventory Overview</h1>
            <p class="mt-1 text-sm text-slate-600">
                Ringkasan metrik aktual Master Data gudang dan pemantauan pergerakan stok harian secara real-time.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5 self-start sm:self-auto">
            <a
                href="{{ route('transactions.create') }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-white border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition-colors"
            >
                <i data-lucide="arrow-left-right" class="w-3.5 h-3.5"></i>
                <span>Catat Mutasi Stok</span>
            </a>
            <a
                href="{{ route('products.index') }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-zinc-950 text-white text-xs font-semibold hover:bg-zinc-800 transition-colors"
            >
                <i data-lucide="package" class="w-3.5 h-3.5"></i>
                <span>Kelola Produk</span>
            </a>
        </div>
    </div>

    <!-- Stat Cards Grid (4 Core Metrics) -->
    <section aria-label="Key Inventory Metrics" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-5 mb-8">
        <!-- Card 1: Total Produk -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-2xs hover:border-slate-300 transition-colors">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Produk</p>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($totalProducts) }}</p>
                </div>
                <span class="w-10 h-10 rounded-lg bg-slate-100 border border-slate-200/80 flex items-center justify-center text-slate-700 shrink-0">
                    <i data-lucide="package" class="w-5 h-5"></i>
                </span>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500">SKU Aktif Terdaftar</span>
                <span class="font-semibold text-slate-700">{{ number_format($categoriesCount) }} Kategori</span>
            </div>
        </div>

        <!-- Card 2: Stok Rendah -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-2xs hover:border-slate-300 transition-colors">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Stok Rendah</p>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-amber-600">{{ number_format($lowStockCount) }}</p>
                </div>
                <span class="w-10 h-10 rounded-lg bg-amber-50 border border-amber-200/80 flex items-center justify-center text-amber-600 shrink-0">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                </span>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500">Di bawah / sama dengan minimum</span>
                <a href="{{ route('products.index', ['stock_status' => 'low']) }}" class="font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200/60 hover:bg-amber-100 transition-colors">
                    Perlu Restock
                </a>
            </div>
        </div>

        <!-- Card 3: Barang Masuk Hari Ini -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-2xs hover:border-slate-300 transition-colors">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Barang Masuk Hari Ini</p>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($stockInToday) }}</p>
                </div>
                <span class="w-10 h-10 rounded-lg bg-emerald-50 border border-emerald-200/80 flex items-center justify-center text-emerald-600 shrink-0">
                    <i data-lucide="arrow-down-left" class="w-5 h-5"></i>
                </span>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500">Total Unit Inbound</span>
                <a href="{{ route('transactions.index', ['type' => 'in']) }}" class="font-semibold text-emerald-700 hover:underline">
                    Lihat Riwayat &rarr;
                </a>
            </div>
        </div>

        <!-- Card 4: Barang Keluar Hari Ini -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-2xs hover:border-slate-300 transition-colors">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Barang Keluar Hari Ini</p>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($stockOutToday) }}</p>
                </div>
                <span class="w-10 h-10 rounded-lg bg-sky-50 border border-sky-200/80 flex items-center justify-center text-sky-600 shrink-0">
                    <i data-lucide="arrow-up-right" class="w-5 h-5"></i>
                </span>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500">Total Unit Outbound</span>
                <a href="{{ route('transactions.index', ['type' => 'out']) }}" class="font-semibold text-sky-700 hover:underline">
                    Lihat Riwayat &rarr;
                </a>
            </div>
        </div>
    </section>

    <!-- Operational Grid: Recent Inventory Activity & Low Stock Watchlist -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Left Column (7 cols): Aktivitas Inventaris Terbaru -->
        <section aria-label="Recent Inventory Activity" class="lg:col-span-7 bg-white rounded-xl border border-slate-200/90 shadow-2xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200/80 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Aktivitas Inventaris Terbaru</h2>
                    <p class="text-xs text-slate-500">5 transaksi pergerakan stok terakhir yang tercatat di gudang.</p>
                </div>
                <a href="{{ route('transactions.index') }}" class="text-xs font-semibold text-slate-700 hover:text-slate-950">
                    Semua Riwayat &rarr;
                </a>
            </div>

            <div class="divide-y divide-slate-200/70">
                @forelse ($recentMutations as $mutation)
                    @php
                        $unitSymbol = $mutation->product?->unit?->symbol ?? 'unit';
                        $delta = $mutation->signedDelta();
                    @endphp
                    <div class="px-5 py-3.5 hover:bg-slate-50/70 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="min-w-0 space-y-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $mutation->reference_number }}
                                </span>

                                @if ($mutation->type === 'in')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                                        <i data-lucide="arrow-down-left" class="w-3 h-3"></i>
                                        <span>Stock In</span>
                                    </span>
                                @elseif ($mutation->type === 'out')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-red-50 text-red-700 border border-red-200/80">
                                        <i data-lucide="arrow-up-right" class="w-3 h-3"></i>
                                        <span>Stock Out</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200/80">
                                        <i data-lucide="sliders-horizontal" class="w-3 h-3"></i>
                                        <span>Adjustment</span>
                                    </span>
                                @endif

                                <span class="text-[11px] text-slate-400">
                                    &bull; {{ $mutation->created_at?->diffForHumans() }}
                                </span>
                            </div>

                            <p class="text-sm font-semibold text-slate-900 truncate">
                                {{ $mutation->product?->name ?? 'Produk Terhapus' }}
                                <span class="text-xs font-mono font-normal text-slate-400">({{ $mutation->product?->sku ?? '-' }})</span>
                            </p>

                            <p class="text-xs text-slate-500">
                                Dicatat oleh <span class="font-medium text-slate-700">{{ $mutation->user?->name ?? 'System' }}</span>
                                @if ($mutation->notes)
                                    &mdash; <span class="text-slate-500">{{ $mutation->notes }}</span>
                                @endif
                            </p>
                        </div>

                        <div class="sm:text-right shrink-0">
                            <div
                                @class([
                                    'text-sm font-bold',
                                    'text-emerald-600' => $delta > 0,
                                    'text-red-600' => $delta < 0,
                                    'text-amber-600' => $delta === 0,
                                ])
                            >
                                {{ $delta > 0 ? '+' . number_format($mutation->quantity) : ($delta < 0 ? '-' . number_format($mutation->quantity) : '0') }}
                                <span class="text-xs font-medium text-slate-500">{{ $unitSymbol }}</span>
                            </div>
                            <div class="text-[11px] font-mono text-slate-400 mt-0.5">
                                {{ $mutation->previous_stock }} &rarr; {{ $mutation->current_stock }} {{ $unitSymbol }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-xs text-slate-500">
                        Belum ada aktivitas mutasi stok yang tercatat.
                    </div>
                @endforelse
            </div>
        </section>

        <!-- Right Column (5 cols): Produk Perlu Restock -->
        <section aria-label="Low Stock Alert List" class="lg:col-span-5 bg-white rounded-xl border border-slate-200/90 shadow-2xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200/80 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900 flex items-center gap-1.5">
                        <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-500"></i>
                        <span>Produk Perlu Restock</span>
                    </h2>
                    <p class="text-xs text-slate-500">5 produk dengan stok paling kritis (&le; batas minimum).</p>
                </div>
                <a href="{{ route('products.index', ['stock_status' => 'low']) }}" class="text-xs font-semibold text-slate-700 hover:text-slate-950">
                    Lihat Semua &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                    <thead class="bg-slate-50/80 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Produk</th>
                            <th class="px-4 py-3 text-right">Stok / Min</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70">
                        @forelse ($lowStockProducts as $item)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-4 py-3.5">
                                    <p class="font-semibold text-slate-900 leading-snug">{{ $item->name }}</p>
                                    <p class="text-[11px] font-mono text-slate-400 mt-0.5">
                                        {{ $item->sku }} &bull; {{ $item->category?->name }}
                                    </p>
                                </td>
                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    <span class="font-bold text-amber-600">{{ $item->stock }}</span>
                                    <span class="text-slate-400">/ {{ $item->minimum_stock }} {{ $item->unit?->symbol }}</span>
                                </td>
                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    <a
                                        href="{{ route('transactions.create', ['product_id' => $item->id, 'type' => 'in']) }}"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200/80 font-semibold hover:bg-emerald-100 transition-colors"
                                    >
                                        <i data-lucide="plus" class="w-3 h-3"></i>
                                        <span>Restock</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-slate-500">
                                    Seluruh stok produk berada di atas batas minimum.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endcomponent
