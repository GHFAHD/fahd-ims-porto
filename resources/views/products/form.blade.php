@component('layouts.dashboard', [
    'title' => 'Products',
    'breadcrumb' => $isEdit ? 'Edit Produk: ' . $product->sku : 'Tambah Produk Baru',
])
    <div class="max-w-3xl">
        <!-- Back Link & Heading -->
        <div class="mb-6">
            <a
                href="{{ route('products.index') }}"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-900 mb-3 transition-colors"
            >
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                <span>Kembali ke Katalog Produk</span>
            </a>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                {{ $isEdit ? 'Perbarui Data Produk' : 'Tambah Produk Master Data' }}
            </h1>
            <p class="mt-1 text-sm text-slate-600">
                Pastikan kode SKU unik dan batas minimum stok diisi dengan tepat untuk pemantauan otomatis.
            </p>
        </div>

        @if (isset($errors) && $errors->any())
            <div class="mb-6 rounded-xl bg-red-50 border border-red-200/80 p-4 flex items-start gap-3" role="alert">
                <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 shrink-0 mt-0.5"></i>
                <div class="text-xs text-red-800 space-y-1">
                    <p class="font-semibold">Terdapat kesalahan input:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form
            method="POST"
            action="{{ $isEdit ? route('products.update', $product) : route('products.store') }}"
            class="bg-white rounded-xl border border-slate-200/90 shadow-2xs p-6 space-y-5"
        >
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="sku" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Kode SKU <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="sku"
                        name="sku"
                        value="{{ old('sku', $product->sku) }}"
                        required
                        placeholder="PRD-XXX-001"
                        class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-mono text-slate-900 focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900/10"
                    >
                </div>

                <div class="sm:col-span-2">
                    <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nama Produk <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $product->name) }}"
                        required
                        placeholder="Contoh: Processor AMD Ryzen 7 7700X"
                        class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900/10"
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="category_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Kategori <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="category_id"
                        name="category_id"
                        required
                        class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 bg-white focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900/10"
                    >
                        <option value="">Pilih Kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) old('category_id', $product->category_id) === $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="unit_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Satuan Ukur <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="unit_id"
                        name="unit_id"
                        required
                        class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 bg-white focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900/10"
                    >
                        <option value="">Pilih Satuan</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" @selected((int) old('unit_id', $product->unit_id) === $unit->id)>
                                {{ $unit->name }} ({{ $unit->symbol }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="supplier_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Supplier Utama
                    </label>
                    <select
                        id="supplier_id"
                        name="supplier_id"
                        class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 bg-white focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900/10"
                    >
                        <option value="">-- Tanpa Supplier --</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected((int) old('supplier_id', $product->supplier_id) === $supplier->id)>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-2 border-t border-slate-100">
                <div>
                    <label for="stock" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Stok Saat Ini <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="number"
                        id="stock"
                        name="stock"
                        min="0"
                        value="{{ old('stock', $product->stock) }}"
                        required
                        class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900/10"
                    >
                </div>

                <div>
                    <label for="minimum_stock" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Stok Minimum <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="number"
                        id="minimum_stock"
                        name="minimum_stock"
                        min="0"
                        value="{{ old('minimum_stock', $product->minimum_stock) }}"
                        required
                        class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900/10"
                    >
                </div>

                <div>
                    <label for="purchase_price" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Harga Beli (Rp) <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="number"
                        id="purchase_price"
                        name="purchase_price"
                        min="0"
                        step="0.01"
                        value="{{ old('purchase_price', $product->purchase_price) }}"
                        required
                        class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900/10"
                    >
                </div>

                <div>
                    <label for="selling_price" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Harga Jual (Rp) <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="number"
                        id="selling_price"
                        name="selling_price"
                        min="0"
                        step="0.01"
                        value="{{ old('selling_price', $product->selling_price) }}"
                        required
                        class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900/10"
                    >
                </div>
            </div>

            <div class="pt-2">
                <label class="inline-flex items-center gap-2.5 cursor-pointer select-none">
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        @checked(old('is_active', $product->is_active))
                        class="w-4 h-4 rounded border-slate-300 text-zinc-900 focus:ring-zinc-900"
                    >
                    <span class="text-xs font-medium text-slate-700">Produk aktif dan dapat digunakan pada transaksi inventaris</span>
                </label>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a
                    href="{{ route('products.index') }}"
                    class="px-4 py-2 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors"
                >
                    Batal
                </a>
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-zinc-950 px-4 py-2 text-xs font-semibold text-white hover:bg-zinc-800 transition-colors cursor-pointer"
                >
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>{{ $isEdit ? 'Simpan Perubahan' : 'Simpan Produk' }}</span>
                </button>
            </div>
        </form>
    </div>
@endcomponent

