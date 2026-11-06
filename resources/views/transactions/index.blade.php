@component('layouts.dashboard', ['title' => 'Stock In/Out', 'breadcrumb' => 'Stock Movement History'])
    <!-- Page Heading & Action Buttons -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Transaksi &amp; Mutasi Stok</h1>
            <p class="mt-1 text-sm text-slate-600">
                Riwayat audit pergerakan barang masuk (Inbound), barang keluar (Outbound), dan penyesuaian stok fisik (Stock Opname).
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 self-start sm:self-auto">
            <a
                href="{{ route('transactions.create', ['type' => 'in']) }}"
                class="inline-flex items-center gap-2 rounded-xl bg-zinc-950 px-4 py-2.5 text-xs font-semibold text-white shadow-2xs hover:bg-zinc-800 transition-colors"
            >
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Catat Transaksi Stok</span>
            </a>
        </div>
    </div>

    <!-- Flash Status Notification -->
    @if (session('status'))
        <div class="mb-6 rounded-xl bg-emerald-50 border border-emerald-200/80 p-4 flex items-center gap-3" role="status">
            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0"></i>
            <p class="text-xs font-medium text-emerald-800">{{ session('status') }}</p>
        </div>
    @endif

    <!-- Transaction KPI Summary Cards -->
    <section aria-label="Stock Mutation Summary" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Total Dokumen Mutasi</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($totalMutationsCount) }}</p>
            </div>
            <span class="w-10 h-10 rounded-lg bg-slate-100 border border-slate-200/80 flex items-center justify-center text-slate-700">
                <i data-lucide="arrow-left-right" class="w-5 h-5"></i>
            </span>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Unit Masuk Hari Ini</p>
                <p class="mt-1 text-2xl font-bold text-emerald-600">+{{ number_format($totalInToday) }}</p>
            </div>
            <span class="w-10 h-10 rounded-lg bg-emerald-50 border border-emerald-200/80 flex items-center justify-center text-emerald-600">
                <i data-lucide="arrow-down-left" class="w-5 h-5"></i>
            </span>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Unit Keluar Hari Ini</p>
                <p class="mt-1 text-2xl font-bold text-red-600">-{{ number_format($totalOutToday) }}</p>
            </div>
            <span class="w-10 h-10 rounded-lg bg-red-50 border border-red-200/80 flex items-center justify-center text-red-600">
                <i data-lucide="arrow-up-right" class="w-5 h-5"></i>
            </span>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Log Adjustment</p>
                <p class="mt-1 text-2xl font-bold text-amber-600">{{ number_format($totalAdjustmentsCount) }}</p>
            </div>
            <span class="w-10 h-10 rounded-lg bg-amber-50 border border-amber-200/80 flex items-center justify-center text-amber-600">
                <i data-lucide="sliders-horizontal" class="w-5 h-5"></i>
            </span>
        </div>
    </section>

    <!-- Search & Filter Bar -->
    <section class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-2xs mb-6">
        <form method="GET" action="{{ route('transactions.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
            <div class="lg:col-span-4">
                <label for="search" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-600 mb-1">
                    Cari Referensi / SKU / Catatan
                </label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </span>
                    <input
                        type="text"
                        id="search"
                        name="search"
                        value="{{ $filters['search'] }}"
                        placeholder="Contoh: IN-2026, PRD-CPU-001, PO..."
                        class="block w-full rounded-lg border border-slate-300 pl-9 pr-3 py-2 text-xs text-slate-900 placeholder:text-slate-400 focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900/10"
                    >
                </div>
            </div>

            <div class="lg:col-span-3">
                <label for="type" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-600 mb-1">
                    Tipe Transaksi
                </label>
                <select
                    id="type"
                    name="type"
                    class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 bg-white focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900/10"
                >
                    <option value="">Semua Tipe</option>
                    <option value="in" @selected($filters['type'] === 'in')>Stock In (Barang Masuk)</option>
                    <option value="out" @selected($filters['type'] === 'out')>Stock Out (Barang Keluar)</option>
                    <option value="adjustment" @selected($filters['type'] === 'adjustment')>Adjustment (Penyesuaian)</option>
                </select>
            </div>

            <div class="lg:col-span-3">
                <label for="product_id" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-600 mb-1">
                    Filter Produk
                </label>
                <select
                    id="product_id"
                    name="product_id"
                    class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 bg-white focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900/10"
                >
                    <option value="">Semua Produk</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" @selected((string) $filters['product_id'] === (string) $product->id)>
                            {{ $product->sku }} — {{ $product->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2 flex items-center gap-2">
                <button
                    type="submit"
                    class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg bg-zinc-900 px-3 py-2 text-xs font-semibold text-white hover:bg-zinc-800 transition-colors cursor-pointer"
                >
                    <span>Filter</span>
                </button>
                @if ($filters['search'] !== '' || $filters['type'] || $filters['product_id'])
                    <a
                        href="{{ route('transactions.index') }}"
                        class="inline-flex items-center justify-center p-2 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 transition-colors"
                        title="Reset Filter"
                    >
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>
        </form>
    </section>

    <!-- Stock Mutations Table -->
    <section class="bg-white rounded-xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200/80 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">Jurnal Pergerakan Stok (Audit Trail)</h2>
                <p class="text-xs text-slate-500">Menampilkan {{ $mutations->count() }} dari total {{ $mutations->total() }} riwayat transaksi.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-slate-50/80 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th scope="col" class="px-5 py-3.5">No. Referensi &amp; Waktu</th>
                        <th scope="col" class="px-5 py-3.5">Produk</th>
                        <th scope="col" class="px-5 py-3.5 text-center">Tipe Mutasi</th>
                        <th scope="col" class="px-5 py-3.5 text-right">Kuantitas</th>
                        <th scope="col" class="px-5 py-3.5 text-right">Snapshot Stok</th>
                        <th scope="col" class="px-5 py-3.5">Pencatat</th>
                        <th scope="col" class="px-5 py-3.5">Catatan / Dokumen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/70 bg-white">
                    @forelse ($mutations as $mutation)
                        @php
                            $unitSymbol = $mutation->product?->unit?->symbol ?? 'unit';
                            $delta = $mutation->signedDelta();
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <!-- Reference & Timestamp -->
                            <td class="px-5 py-4 whitespace-nowrap align-top">
                                <span class="inline-flex items-center font-mono text-xs font-semibold px-2 py-0.5 rounded bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $mutation->reference_number }}
                                </span>
                                <p class="mt-1 text-[11px] text-slate-400">
                                    {{ $mutation->created_at?->format('d M Y, H:i') }}
                                </p>
                            </td>

                            <!-- Product -->
                            <td class="px-5 py-4 align-top">
                                <p class="font-semibold text-slate-900 text-sm leading-snug">
                                    {{ $mutation->product?->name ?? 'Produk Terhapus' }}
                                </p>
                                <p class="mt-0.5 text-[11px] font-mono text-slate-500">
                                    {{ $mutation->product?->sku ?? '-' }}
                                </p>
                            </td>

                            <!-- Type Badge -->
                            <td class="px-5 py-4 whitespace-nowrap text-center align-top">
                                @if ($mutation->type === 'in')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                                        <i data-lucide="arrow-down-left" class="w-3 h-3"></i>
                                        <span>Stock In</span>
                                    </span>
                                @elseif ($mutation->type === 'out')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-red-50 text-red-700 border border-red-200/80">
                                        <i data-lucide="arrow-up-right" class="w-3 h-3"></i>
                                        <span>Stock Out</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200/80">
                                        <i data-lucide="sliders-horizontal" class="w-3 h-3"></i>
                                        <span>Adjustment</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Quantity -->
                            <td class="px-5 py-4 whitespace-nowrap text-right align-top">
                                <span
                                    @class([
                                        'font-bold text-sm',
                                        'text-emerald-600' => $delta > 0,
                                        'text-red-600' => $delta < 0,
                                        'text-amber-600' => $delta === 0,
                                    ])
                                >
                                    {{ $delta > 0 ? '+' . number_format($mutation->quantity) : ($delta < 0 ? '-' . number_format($mutation->quantity) : '0') }}
                                </span>
                                <span class="text-xs text-slate-500">{{ $unitSymbol }}</span>
                            </td>

                            <!-- Stock Snapshot (Before -> After) -->
                            <td class="px-5 py-4 whitespace-nowrap text-right align-top">
                                <div class="inline-flex items-center gap-1.5 font-mono text-xs">
                                    <span class="text-slate-500">{{ number_format($mutation->previous_stock) }}</span>
                                    <i data-lucide="arrow-right" class="w-3 h-3 text-slate-400"></i>
                                    <span class="font-bold text-slate-900">{{ number_format($mutation->current_stock) }}</span>
                                    <span class="text-[11px] font-sans text-slate-400">{{ $unitSymbol }}</span>
                                </div>
                            </td>

                            <!-- User -->
                            <td class="px-5 py-4 whitespace-nowrap align-top">
                                <p class="font-semibold text-slate-800">{{ $mutation->user?->name ?? 'System' }}</p>
                                <p class="text-[11px] text-slate-400 uppercase">{{ $mutation->user?->role ?? '-' }}</p>
                            </td>

                            <!-- Notes -->
                            <td class="px-5 py-4 align-top max-w-xs">
                                <p class="text-xs text-slate-600 leading-relaxed">
                                    {{ $mutation->notes ?: '—' }}
                                </p>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-slate-500">
                                <p class="font-semibold text-slate-700">Belum ada riwayat transaksi stok yang ditemukan.</p>
                                <p class="mt-1 text-xs">Klik tombol "Catat Transaksi Stok" untuk merekam pergerakan barang.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($mutations->hasPages())
            <div class="px-5 py-3.5 border-t border-slate-200/80 bg-slate-50/50">
                {{ $mutations->links() }}
            </div>
        @endif
    </section>
@endcomponent
