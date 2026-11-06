@component('layouts.dashboard', ['title' => 'Products', 'breadcrumb' => 'Master Data Catalog'])
    <!-- Page Heading & Primary Action -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Master Data Produk</h1>
            <p class="mt-1 text-sm text-slate-600">
                Kelola katalog SKU produk, ambang batas stok minimum, kategori, satuan ukur, dan mitra supplier.
            </p>
        </div>

        <div class="flex items-center gap-2.5 self-start sm:self-auto">
            <a
                href="{{ route('products.create') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-zinc-950 px-4 py-2.5 text-xs font-semibold text-white shadow-2xs hover:bg-zinc-800 transition-colors"
            >
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Tambah Produk</span>
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

    <!-- Master Data KPI Summary Strip -->
    <section aria-label="Master Data Summary" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Total SKU Produk</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($totalProducts) }}</p>
            </div>
            <span class="w-10 h-10 rounded-lg bg-slate-100 border border-slate-200/80 flex items-center justify-center text-slate-700">
                <i data-lucide="package" class="w-5 h-5"></i>
            </span>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Item Stok Rendah</p>
                <p class="mt-1 text-2xl font-bold text-amber-600">{{ number_format($lowStockCount) }}</p>
            </div>
            <span class="w-10 h-10 rounded-lg bg-amber-50 border border-amber-200/80 flex items-center justify-center text-amber-600">
                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
            </span>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Kategori &amp; Satuan</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">
                    {{ $categories->count() }} <span class="text-sm font-medium text-slate-400">/ {{ $units->count() }} unit</span>
                </p>
            </div>
            <span class="w-10 h-10 rounded-lg bg-slate-100 border border-slate-200/80 flex items-center justify-center text-slate-700">
                <i data-lucide="tag" class="w-5 h-5"></i>
            </span>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Supplier Resmi</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ $suppliers->count() }}</p>
            </div>
            <span class="w-10 h-10 rounded-lg bg-slate-100 border border-slate-200/80 flex items-center justify-center text-slate-700">
                <i data-lucide="truck" class="w-5 h-5"></i>
            </span>
        </div>
    </section>

    <!-- Filter & Search Bar -->
    <section class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-2xs mb-6">
        <form method="GET" action="{{ route('products.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
            <div class="lg:col-span-5">
                <label for="search" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-600 mb-1">
                    Cari SKU / Nama Produk
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
                        placeholder="Contoh: PRD-CPU-001 atau Ryzen..."
                        class="block w-full rounded-lg border border-slate-300 pl-9 pr-3 py-2 text-xs text-slate-900 placeholder:text-slate-400 focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900/10"
                    >
                </div>
            </div>

            <div class="lg:col-span-3">
                <label for="category_id" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-600 mb-1">
                    Kategori
                </label>
                <select
                    id="category_id"
                    name="category_id"
                    class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 bg-white focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900/10"
                >
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) $filters['category_id'] === (string) $category->id)>
                            {{ $category->name }} ({{ $category->products_count }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <label for="stock_status" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-600 mb-1">
                    Status Stok
                </label>
                <select
                    id="stock_status"
                    name="stock_status"
                    class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-900 bg-white focus:border-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-900/10"
                >
                    <option value="">Semua Status</option>
                    <option value="low" @selected($filters['stock_status'] === 'low')>Stok Rendah (&le; Min)</option>
                    <option value="normal" @selected($filters['stock_status'] === 'normal')>Stok Normal</option>
                </select>
            </div>

            <div class="lg:col-span-2 flex items-center gap-2">
                <button
                    type="submit"
                    class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg bg-zinc-900 px-3 py-2 text-xs font-semibold text-white hover:bg-zinc-800 transition-colors cursor-pointer"
                >
                    <span>Terapkan</span>
                </button>
                @if ($filters['search'] !== '' || $filters['category_id'] || $filters['stock_status'])
                    <a
                        href="{{ route('products.index') }}"
                        class="inline-flex items-center justify-center p-2 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 transition-colors"
                        title="Reset Filter"
                    >
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>
        </form>
    </section>

    <!-- Products Responsive Table -->
    <section class="bg-white rounded-xl border border-slate-200/90 shadow-2xs overflow-hidden mb-8">
        <div class="px-5 py-4 border-b border-slate-200/80 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">Daftar Inventaris Produk</h2>
                <p class="text-xs text-slate-500">Menampilkan {{ $products->count() }} dari total {{ $products->total() }} produk terdaftar.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-slate-50/80 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th scope="col" class="px-5 py-3.5">SKU</th>
                        <th scope="col" class="px-5 py-3.5">Produk &amp; Supplier</th>
                        <th scope="col" class="px-5 py-3.5">Kategori</th>
                        <th scope="col" class="px-5 py-3.5 text-right">Stok / Min</th>
                        <th scope="col" class="px-5 py-3.5 text-center">Status Stok</th>
                        <th scope="col" class="px-5 py-3.5 text-right">Harga Beli</th>
                        <th scope="col" class="px-5 py-3.5 text-right">Harga Jual</th>
                        <th scope="col" class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/70 bg-white">
                    @forelse ($products as $product)
                        @php
                            $lowStock = $product->isLowStock();
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <!-- SKU -->
                            <td class="px-5 py-4 whitespace-nowrap align-top">
                                <span class="inline-flex items-center font-mono text-xs font-semibold px-2 py-1 rounded bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $product->sku }}
                                </span>
                                @if (! $product->is_active)
                                    <span class="mt-1 block text-[10px] font-medium text-slate-400">Nonaktif</span>
                                @endif
                            </td>

                            <!-- Product Name & Supplier -->
                            <td class="px-5 py-4 align-top">
                                <p class="font-semibold text-slate-900 text-sm leading-snug">{{ $product->name }}</p>
                                <p class="mt-1 text-xs text-slate-500 flex items-center gap-1.5">
                                    <i data-lucide="truck" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                    <span>{{ $product->supplier?->name ?? 'Tanpa Supplier' }}</span>
                                </p>
                            </td>

                            <!-- Category -->
                            <td class="px-5 py-4 whitespace-nowrap align-top">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-slate-100 text-slate-700 font-medium">
                                    {{ $product->category?->name ?? '-' }}
                                </span>
                            </td>

                            <!-- Stock & Minimum Stock -->
                            <td class="px-5 py-4 whitespace-nowrap text-right align-top">
                                <div class="font-bold text-sm {{ $lowStock ? 'text-amber-600' : 'text-slate-900' }}">
                                    {{ number_format($product->stock) }}
                                    <span class="text-xs font-medium text-slate-500">{{ $product->unit?->symbol }}</span>
                                </div>
                                <div class="text-[11px] text-slate-400 mt-0.5">
                                    Min: {{ number_format($product->minimum_stock) }} {{ $product->unit?->symbol }}
                                </div>
                            </td>

                            <!-- Stock Status Badge -->
                            <td class="px-5 py-4 whitespace-nowrap text-center align-top">
                                @if ($lowStock)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200/80">
                                        <i data-lucide="alert-triangle" class="w-3 h-3"></i>
                                        <span>Stok Rendah</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                                        <i data-lucide="check-circle-2" class="w-3 h-3"></i>
                                        <span>Stok Normal</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Purchase Price -->
                            <td class="px-5 py-4 whitespace-nowrap text-right align-top font-medium text-slate-600">
                                Rp {{ number_format((float) $product->purchase_price, 0, ',', '.') }}
                            </td>

                            <!-- Selling Price -->
                            <td class="px-5 py-4 whitespace-nowrap text-right align-top font-semibold text-slate-900">
                                Rp {{ number_format((float) $product->selling_price, 0, ',', '.') }}
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-4 whitespace-nowrap text-right align-top">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    <a
                                        href="{{ route('products.edit', $product) }}"
                                        class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors"
                                        title="Edit Produk"
                                    >
                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('products.destroy', $product) }}"
                                        onsubmit="return confirm('Hapus produk {{ $product->sku }} dari Master Data?');"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="p-1.5 rounded-lg border border-slate-200 text-slate-500 hover:text-red-600 hover:bg-red-50 hover:border-red-200 transition-colors cursor-pointer"
                                            title="Hapus Produk"
                                        >
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-10 text-center text-slate-500">
                                <p class="font-semibold text-slate-700">Tidak ada data produk yang ditemukan.</p>
                                <p class="mt-1 text-xs">Coba ubah kata kunci pencarian atau tambahkan produk baru.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($products->hasPages())
            <div class="px-5 py-3.5 border-t border-slate-200/80 bg-slate-50/50">
                {{ $products->links() }}
            </div>
        @endif
    </section>

    <!-- Supporting Master Data Reference Grid (Categories, Units, Suppliers) -->
    <section aria-label="Supporting Master Data Entities" class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Categories Card -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-2xs">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                    <i data-lucide="tag" class="w-4 h-4 text-slate-400"></i>
                    <span>Master Kategori ({{ $categories->count() }})</span>
                </h3>
            </div>
            <ul class="divide-y divide-slate-100 text-xs">
                @foreach ($categories as $category)
                    <li class="py-2.5 flex items-center justify-between gap-2">
                        <div>
                            <p class="font-semibold text-slate-800">{{ $category->name }}</p>
                            <p class="text-[11px] text-slate-400 font-mono">{{ $category->slug }}</p>
                        </div>
                        <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-medium">
                            {{ $category->products_count }} SKU
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>

        <!-- Units Card -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-2xs">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                    <i data-lucide="scale" class="w-4 h-4 text-slate-400"></i>
                    <span>Master Satuan Ukur ({{ $units->count() }})</span>
                </h3>
            </div>
            <div class="grid grid-cols-2 gap-2.5">
                @foreach ($units as $unit)
                    <div class="p-3 rounded-lg border border-slate-200/80 bg-slate-50/60 flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-800">{{ $unit->name }}</span>
                        <span class="text-[11px] font-mono font-semibold px-1.5 py-0.5 rounded bg-white border border-slate-200 text-slate-600">
                            {{ $unit->symbol }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Suppliers Card -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-2xs">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                    <i data-lucide="truck" class="w-4 h-4 text-slate-400"></i>
                    <span>Master Supplier ({{ $suppliers->count() }})</span>
                </h3>
            </div>
            <ul class="divide-y divide-slate-100 text-xs">
                @foreach ($suppliers as $supplier)
                    <li class="py-2.5">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-semibold text-slate-800 truncate">{{ $supplier->name }}</p>
                            <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-medium shrink-0">
                                {{ $supplier->products_count }} SKU
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            PIC: {{ $supplier->contact_name ?? '-' }} &bull; {{ $supplier->phone }}
                        </p>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endcomponent

