@component('layouts.dashboard', ['title' => 'Reports', 'breadcrumb' => 'Inventory Audit & Export'])
    <!-- Official Print Header (Only visible in @media print / PDF output) -->
    <div class="hidden print:block mb-6 pb-4 border-b-2 border-slate-900">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500">FAHD IMS &mdash; CENTRAL DISTRIBUTION WAREHOUSE (WH-01)</p>
                <h1 class="mt-1 text-xl font-bold tracking-tight text-slate-950 uppercase">Laporan Audit Mutasi Stok Inventaris</h1>
                <p class="mt-1 text-xs text-slate-600">
                    Periode: <span class="font-semibold text-slate-900">{{ \Illuminate\Support\Carbon::parse($filters['start_date'])->format('d M Y') }}</span>
                    s/d
                    <span class="font-semibold text-slate-900">{{ \Illuminate\Support\Carbon::parse($filters['end_date'])->format('d M Y') }}</span>
                    &bull;
                    Tipe: <span class="font-semibold text-slate-900">{{ $filters['type'] !== '' ? strtoupper($filters['type']) : 'Semua Tipe' }}</span>
                    &bull;
                    Kategori: <span class="font-semibold text-slate-900">{{ $selectedCategory?->name ?? 'Semua Kategori' }}</span>
                </p>
            </div>
            <div class="text-right text-xs text-slate-600">
                <p>Tanggal Cetak: <span class="font-mono font-semibold text-slate-900">{{ now()->format('d/m/Y H:i') }}</span></p>
                <p>Dicetak Oleh: <span class="font-semibold text-slate-900">{{ auth()->user()?->name ?? 'Administrator' }}</span></p>
            </div>
        </div>
    </div>

    <!-- Screen Heading & Export / Print Actions -->
    <div class="no-print mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Laporan &amp; Ekspor Data Mutasi</h1>
            <p class="mt-1 text-sm text-slate-600">
                Rekapitulasi pergerakan inventaris berdasarkan rentang tanggal, tipe transaksi, dan kategori produk untuk kebutuhan audit.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 self-start sm:self-auto">
            <a
                href="{{ route('reports.export', request()->query()) }}"
                class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-semibold text-white shadow-2xs hover:bg-emerald-700 transition-colors"
            >
                <i data-lucide="download" class="w-4 h-4"></i>
                <span>Export CSV</span>
            </a>

            <button
                type="button"
                onclick="window.print()"
                class="inline-flex items-center gap-2 rounded-xl bg-zinc-950 px-4 py-2.5 text-xs font-semibold text-white shadow-2xs hover:bg-zinc-800 transition-colors cursor-pointer"
            >
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak Laporan / PDF</span>
            </button>
        </div>
    </div>

    <!-- Date-Range & Attribute Filter Bar -->
    <section aria-label="Filter Laporan" class="no-print bg-white rounded-xl border border-slate-200/90 p-4 shadow-2xs mb-6">
        <form method="GET" action="{{ route('reports.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
            <!-- Start Date -->
            <div class="lg:col-span-3">
                <label for="start_date" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-600 mb-1">
                    Tanggal Mulai
                </label>
                <input
                    type="date"
                    id="start_date"
                    name="start_date"
                    value="{{ $filters['start_date'] }}"
                    class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 bg-white focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900/10"
                >
            </div>

            <!-- End Date -->
            <div class="lg:col-span-3">
                <label for="end_date" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-600 mb-1">
                    Tanggal Akhir
                </label>
                <input
                    type="date"
                    id="end_date"
                    name="end_date"
                    value="{{ $filters['end_date'] }}"
                    class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 bg-white focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900/10"
                >
            </div>

            <!-- Transaction Type -->
            <div class="lg:col-span-2">
                <label for="type" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-600 mb-1">
                    Tipe Mutasi
                </label>
                <select
                    id="type"
                    name="type"
                    class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 bg-white focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900/10"
                >
                    <option value="">Semua Tipe</option>
                    <option value="in" @selected($filters['type'] === 'in')>Stock In (Masuk)</option>
                    <option value="out" @selected($filters['type'] === 'out')>Stock Out (Keluar)</option>
                    <option value="adjustment" @selected($filters['type'] === 'adjustment')>Adjustment (Opname)</option>
                </select>
            </div>

            <!-- Product Category -->
            <div class="lg:col-span-2">
                <label for="category_id" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-600 mb-1">
                    Kategori Produk
                </label>
                <select
                    id="category_id"
                    name="category_id"
                    class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 bg-white focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900/10"
                >
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((int) $filters['category_id'] === $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Submit & Reset -->
            <div class="lg:col-span-2 flex items-center gap-2">
                <button
                    type="submit"
                    class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg bg-zinc-900 px-3 py-2 text-xs font-semibold text-white hover:bg-zinc-800 transition-colors cursor-pointer"
                >
                    <i data-lucide="search" class="w-3.5 h-3.5"></i>
                    <span>Terapkan</span>
                </button>
                <a
                    href="{{ route('reports.index') }}"
                    class="inline-flex items-center justify-center p-2 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 transition-colors"
                    title="Reset Filter Periode Default"
                >
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </a>
            </div>
        </form>
    </section>

    <!-- Period Summary Cards -->
    <section aria-label="Ringkasan Periode Laporan" class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6 print:grid-cols-3 print:gap-3">
        <!-- Total Transaksi -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-2xs flex items-center justify-between print:border-slate-300 print:shadow-none">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Total Transaksi (Periode)</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($summary['total_transactions']) }}</p>
                <p class="mt-1 text-[11px] text-slate-400">
                    Termasuk {{ number_format($summary['total_adjustments']) }} dokumen adjustment
                </p>
            </div>
            <span class="no-print w-10 h-10 rounded-lg bg-slate-100 border border-slate-200/80 flex items-center justify-center text-slate-700 shrink-0">
                <i data-lucide="file-bar-chart" class="w-5 h-5"></i>
            </span>
        </div>

        <!-- Total Unit Masuk -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-2xs flex items-center justify-between print:border-slate-300 print:shadow-none">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Total Unit Masuk (Inbound)</p>
                <p class="mt-1 text-2xl font-bold text-emerald-600">+{{ number_format($summary['total_units_in']) }}</p>
                <p class="mt-1 text-[11px] text-slate-400">Akumulasi penambahan stok</p>
            </div>
            <span class="no-print w-10 h-10 rounded-lg bg-emerald-50 border border-emerald-200/80 flex items-center justify-center text-emerald-600 shrink-0">
                <i data-lucide="arrow-down-left" class="w-5 h-5"></i>
            </span>
        </div>

        <!-- Total Unit Keluar -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-2xs flex items-center justify-between print:border-slate-300 print:shadow-none">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Total Unit Keluar (Outbound)</p>
                <p class="mt-1 text-2xl font-bold text-red-600">-{{ number_format($summary['total_units_out']) }}</p>
                <p class="mt-1 text-[11px] text-slate-400">Akumulasi pengeluaran stok</p>
            </div>
            <span class="no-print w-10 h-10 rounded-lg bg-red-50 border border-red-200/80 flex items-center justify-center text-red-600 shrink-0">
                <i data-lucide="arrow-up-right" class="w-5 h-5"></i>
            </span>
        </div>
    </section>

    <!-- Detailed Report Table -->
    <section class="bg-white rounded-xl border border-slate-200/90 shadow-2xs overflow-hidden print:border-slate-300 print:shadow-none">
        <div class="px-5 py-4 border-b border-slate-200/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">Rincian Mutasi Stok Terfilter</h2>
                <p class="text-xs text-slate-500">
                    Periode {{ \Illuminate\Support\Carbon::parse($filters['start_date'])->format('d M Y') }} &ndash; {{ \Illuminate\Support\Carbon::parse($filters['end_date'])->format('d M Y') }}
                    ({{ $mutations->total() }} transaksi ditemukan).
                </p>
            </div>
            <div class="no-print text-xs text-slate-500">
                Menampilkan {{ $mutations->count() }} baris pada halaman ini
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs print:text-[11px]">
                <thead class="bg-slate-50/80 text-[11px] font-semibold uppercase tracking-wider text-slate-500 print:bg-slate-100 print:text-slate-900">
                    <tr>
                        <th scope="col" class="px-4 py-3">Tanggal</th>
                        <th scope="col" class="px-4 py-3">No. Referensi</th>
                        <th scope="col" class="px-4 py-3 text-center">Tipe</th>
                        <th scope="col" class="px-4 py-3">SKU &amp; Nama Produk</th>
                        <th scope="col" class="px-4 py-3">Kategori</th>
                        <th scope="col" class="px-4 py-3 text-right">Qty</th>
                        <th scope="col" class="px-4 py-3 text-right">Stok Awal</th>
                        <th scope="col" class="px-4 py-3 text-right">Stok Akhir</th>
                        <th scope="col" class="px-4 py-3">Petugas</th>
                        <th scope="col" class="px-4 py-3">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/70 bg-white">
                    @forelse ($mutations as $mutation)
                        @php
                            $unitSymbol = $mutation->product?->unit?->symbol ?? 'unit';
                            $delta = $mutation->signedDelta();
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors break-inside-avoid">
                            <!-- Tanggal -->
                            <td class="px-4 py-3.5 whitespace-nowrap align-top font-mono text-[11px] text-slate-600">
                                {{ $mutation->created_at?->format('Y-m-d H:i') }}
                            </td>

                            <!-- No Referensi -->
                            <td class="px-4 py-3.5 whitespace-nowrap align-top">
                                <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $mutation->reference_number }}
                                </span>
                            </td>

                            <!-- Tipe -->
                            <td class="px-4 py-3.5 whitespace-nowrap text-center align-top">
                                @if ($mutation->type === 'in')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                                        <span>Stock In</span>
                                    </span>
                                @elseif ($mutation->type === 'out')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-red-50 text-red-700 border border-red-200/80">
                                        <span>Stock Out</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200/80">
                                        <span>Adjustment</span>
                                    </span>
                                @endif
                            </td>

                            <!-- SKU & Nama Produk -->
                            <td class="px-4 py-3.5 align-top">
                                <p class="font-semibold text-slate-900 leading-snug">
                                    {{ $mutation->product?->name ?? 'Produk Terhapus' }}
                                </p>
                                <p class="text-[11px] font-mono text-slate-500">
                                    {{ $mutation->product?->sku ?? '-' }}
                                </p>
                            </td>

                            <!-- Kategori -->
                            <td class="px-4 py-3.5 whitespace-nowrap align-top text-slate-700">
                                {{ $mutation->product?->category?->name ?? '-' }}
                            </td>

                            <!-- Qty -->
                            <td class="px-4 py-3.5 whitespace-nowrap text-right align-top font-mono">
                                <span
                                    @class([
                                        'font-bold',
                                        'text-emerald-600' => $delta > 0,
                                        'text-red-600' => $delta < 0,
                                        'text-amber-600' => $delta === 0,
                                    ])
                                >
                                    {{ $delta > 0 ? '+' . number_format($mutation->quantity) : ($delta < 0 ? '-' . number_format($mutation->quantity) : '0') }}
                                </span>
                                <span class="text-[11px] font-sans text-slate-400">{{ $unitSymbol }}</span>
                            </td>

                            <!-- Stok Awal -->
                            <td class="px-4 py-3.5 whitespace-nowrap text-right align-top font-mono text-slate-600">
                                {{ number_format($mutation->previous_stock) }}
                            </td>

                            <!-- Stok Akhir -->
                            <td class="px-4 py-3.5 whitespace-nowrap text-right align-top font-mono font-bold text-slate-900">
                                {{ number_format($mutation->current_stock) }}
                            </td>

                            <!-- Petugas -->
                            <td class="px-4 py-3.5 whitespace-nowrap align-top">
                                <p class="font-medium text-slate-800">{{ $mutation->user?->name ?? 'System' }}</p>
                            </td>

                            <!-- Catatan -->
                            <td class="px-4 py-3.5 align-top max-w-xs text-slate-600">
                                {{ $mutation->notes ?: '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-5 py-10 text-center text-slate-500">
                                <p class="font-semibold text-slate-700">Tidak ada data mutasi stok pada rentang tanggal atau filter ini.</p>
                                <p class="mt-1 text-xs">Silakan sesuaikan filter tanggal, tipe transaksi, atau kategori produk di atas.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($mutations->hasPages())
            <div class="no-print px-5 py-3.5 border-t border-slate-200/80 bg-slate-50/50">
                {{ $mutations->links() }}
            </div>
        @endif
    </section>

    <!-- Official Print Footer / Audit Signatures (Only visible in @media print) -->
    <div class="hidden print:grid print:grid-cols-2 gap-12 mt-10 pt-6 border-t border-slate-300 text-xs text-slate-700 break-inside-avoid">
        <div>
            <p class="font-semibold text-slate-900">Disiapkan Oleh (Petugas Gudang):</p>
            <div class="h-16"></div>
            <p class="font-semibold text-slate-900 underline">{{ auth()->user()?->name ?? '______________________' }}</p>
            <p class="text-[11px] text-slate-500">Tanggal: {{ now()->format('d M Y') }}</p>
        </div>
        <div class="text-right">
            <p class="font-semibold text-slate-900">Diverifikasi Oleh (Kepala Gudang / Auditor):</p>
            <div class="h-16"></div>
            <p class="font-semibold text-slate-900 underline">( ______________________ )</p>
            <p class="text-[11px] text-slate-500">Tanda Tangan &amp; Stempel Resmi</p>
        </div>
    </div>
@endcomponent

