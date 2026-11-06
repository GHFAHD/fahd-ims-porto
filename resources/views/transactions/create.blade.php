@component('layouts.dashboard', ['title' => 'Stock In/Out', 'breadcrumb' => 'Catat Transaksi Stok'])
    <div class="max-w-4xl">
        <!-- Back Link & Heading -->
        <div class="mb-6">
            <a
                href="{{ route('transactions.index') }}"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-900 mb-3 transition-colors"
            >
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                <span>Kembali ke Jurnal Mutasi Stok</span>
            </a>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                Catat Transaksi Pergerakan Stok
            </h1>
            <p class="mt-1 text-sm text-slate-600">
                Seluruh mutasi diproses secara atomik menggunakan penguncian baris (<code class="text-xs font-mono bg-slate-100 px-1.5 py-0.5 rounded">DB::transaction</code> + <code class="text-xs font-mono bg-slate-100 px-1.5 py-0.5 rounded">lockForUpdate</code>).
            </p>
        </div>

        <!-- Validation Error Alert -->
        @if (isset($errors) && $errors->any())
            <div class="mb-6 rounded-xl bg-red-50 border border-red-200/80 p-4 flex items-start gap-3" role="alert">
                <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 shrink-0 mt-0.5"></i>
                <div class="text-xs text-red-800 space-y-1">
                    <p class="font-semibold">Transaksi Dibatalkan — Validasi Gagal:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Form Column -->
            <form
                method="POST"
                action="{{ route('transactions.store') }}"
                id="stock-mutation-form"
                class="lg:col-span-8 bg-white rounded-xl border border-slate-200/90 shadow-2xs p-6 space-y-5"
            >
                @csrf

                <!-- Product Selection -->
                <div>
                    <label for="mutation_product_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Pilih Produk <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="mutation_product_id"
                        name="product_id"
                        required
                        class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 bg-white focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900/10"
                    >
                        <option value="" data-stock="0" data-min-stock="0" data-unit="unit" data-sku="-" data-category="-">
                            -- Pilih Produk dari Katalog Master Data --
                        </option>
                        @foreach ($products as $product)
                            <option
                                value="{{ $product->id }}"
                                data-stock="{{ $product->stock }}"
                                data-min-stock="{{ $product->minimum_stock }}"
                                data-unit="{{ $product->unit?->symbol ?? 'unit' }}"
                                data-sku="{{ $product->sku }}"
                                data-category="{{ $product->category?->name ?? '-' }}"
                                @selected((string) old('product_id', $selectedProductId) === (string) $product->id)
                            >
                                [{{ $product->sku }}] {{ $product->name }} — (Stok: {{ $product->stock }} {{ $product->unit?->symbol }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Transaction Type & Quantity -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="mutation_type" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            Tipe Transaksi <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="mutation_type"
                            name="type"
                            required
                            class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 bg-white focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900/10"
                        >
                            @php
                                $activeType = old('type', $selectedType);
                            @endphp
                            <option value="in" @selected($activeType === 'in')>Stock In — Barang Masuk (+)</option>
                            <option value="out" @selected($activeType === 'out')>Stock Out — Barang Keluar (-)</option>
                            <option value="adjustment" @selected($activeType === 'adjustment')>Adjustment — Penyesuaian Stok Fisik (=)</option>
                        </select>
                        <p id="mutation_type_hint" class="mt-1.5 text-[11px] text-slate-500">
                            Menambahkan jumlah barang masuk ke saldo stok saat ini.
                        </p>
                    </div>

                    <div>
                        <label for="mutation_quantity" id="mutation_quantity_label" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            Kuantitas Mutasi <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="number"
                            id="mutation_quantity"
                            name="quantity"
                            min="0"
                            step="1"
                            value="{{ old('quantity', 1) }}"
                            required
                            class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm font-semibold text-slate-900 focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900/10"
                        >
                        <p id="mutation_quantity_warning" class="hidden mt-1.5 text-[11px] font-semibold text-red-600">
                            Kuantitas keluar melebihi stok yang tersedia!
                        </p>
                    </div>
                </div>

                <!-- Notes / Reference Reason -->
                <div>
                    <label for="notes" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Catatan / Nomor Dokumen Pendukung (PO / Surat Jalan / AlasanOpname)
                    </label>
                    <textarea
                        id="notes"
                        name="notes"
                        rows="3"
                        placeholder="Contoh: Penerimaan barang PO-2026-014 dari PT Nusantara Teknologi Distribusi..."
                        class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900/10"
                    >{{ old('notes') }}</textarea>
                </div>

                <!-- Submit Actions -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a
                        href="{{ route('transactions.index') }}"
                        class="px-4 py-2 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors"
                    >
                        Batal
                    </a>
                    <button
                        type="submit"
                        id="mutation_submit_btn"
                        class="inline-flex items-center gap-2 rounded-lg bg-zinc-950 px-4 py-2.5 text-xs font-semibold text-white hover:bg-zinc-800 transition-colors cursor-pointer"
                    >
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Proses &amp; Simpan Mutasi</span>
                    </button>
                </div>
            </form>

            <!-- Live Stock Snapshot Preview Panel -->
            <aside class="lg:col-span-4 bg-white rounded-xl border border-slate-200/90 shadow-2xs p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Kalkulator Saldo Stok</h2>
                        <p class="text-[11px] text-slate-400 mt-0.5">Simulasi perubahan stok secara real-time</p>
                    </div>
                    <span class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-700">
                        <i data-lucide="package" class="w-4 h-4"></i>
                    </span>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Kode SKU</span>
                        <span id="preview_sku" class="font-mono font-semibold text-slate-800">-</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Kategori</span>
                        <span id="preview_category" class="font-medium text-slate-700">-</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Batas Stok Minimum</span>
                        <span id="preview_min_stock" class="font-semibold text-slate-700">0 unit</span>
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-3">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-medium text-slate-600">Stok Sebelum:</span>
                        <span id="preview_previous_stock" class="font-mono font-bold text-sm text-slate-900">0 unit</span>
                    </div>
                    <div class="flex items-center justify-between text-xs border-t border-slate-200/70 pt-2.5">
                        <span class="font-semibold text-slate-800">Estimasi Stok Akhir:</span>
                        <span id="preview_current_stock" class="font-mono font-bold text-base text-emerald-600">0 unit</span>
                    </div>
                </div>

                <div class="text-[11px] text-slate-500 leading-relaxed bg-slate-50/70 rounded-lg p-3 border border-slate-100">
                    <p class="font-semibold text-slate-700 mb-1">Aturan Keamanan Stok:</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Transaksi <strong>Stock Out</strong> otomatis ditolak jika kuantitas melebihi saldo stok tersedia.</li>
                        <li>Transaksi <strong>Adjustment</strong> menetapkan saldo fisik hasil opname secara langsung.</li>
                    </ul>
                </div>
            </aside>
        </div>
    </div>
@endcomponent

