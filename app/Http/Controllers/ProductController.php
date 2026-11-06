<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a listing of master data products with search and filter support.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $categoryId = $request->query('category_id');
        $stockStatus = $request->query('stock_status');

        $productsQuery = Product::query()
            ->with(['category', 'unit', 'supplier'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($categoryId, function ($query) use ($categoryId): void {
                $query->where('category_id', $categoryId);
            })
            ->when($stockStatus === 'low', function ($query): void {
                $query->whereColumn('stock', '<=', 'minimum_stock');
            })
            ->when($stockStatus === 'normal', function ($query): void {
                $query->whereColumn('stock', '>', 'minimum_stock');
            })
            ->orderBy('sku');

        $products = $productsQuery->paginate(10)->withQueryString();

        $categories = Category::query()->withCount('products')->orderBy('name')->get();
        $units = Unit::query()->orderBy('name')->get();
        $suppliers = Supplier::query()->withCount('products')->orderBy('name')->get();

        $totalProducts = Product::query()->count();
        $lowStockCount = Product::query()->whereColumn('stock', '<=', 'minimum_stock')->count();

        return view('products.index', [
            'products' => $products,
            'categories' => $categories,
            'units' => $units,
            'suppliers' => $suppliers,
            'totalProducts' => $totalProducts,
            'lowStockCount' => $lowStockCount,
            'filters' => [
                'search' => $search,
                'category_id' => $categoryId,
                'stock_status' => $stockStatus,
            ],
        ]);
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(): View
    {
        return view('products.form', [
            'product' => new Product([
                'stock' => 0,
                'minimum_stock' => 5,
                'purchase_price' => 0,
                'selling_price' => 0,
                'is_active' => true,
            ]),
            'categories' => Category::query()->orderBy('name')->get(),
            'units' => Unit::query()->orderBy('name')->get(),
            'suppliers' => Supplier::query()->orderBy('name')->get(),
            'isEdit' => false,
        ]);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:64', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'stock' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['sku'] = strtoupper(trim($validated['sku']));
        $validated['is_active'] = $request->boolean('is_active', true);

        Product::query()->create($validated);

        return redirect()
            ->route('products.index')
            ->with('status', "Produk {$validated['sku']} berhasil ditambahkan ke Master Data.");
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product): View
    {
        return view('products.form', [
            'product' => $product,
            'categories' => Category::query()->orderBy('name')->get(),
            'units' => Unit::query()->orderBy('name')->get(),
            'suppliers' => Supplier::query()->orderBy('name')->get(),
            'isEdit' => true,
        ]);
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'sku' => [
                'required',
                'string',
                'max:64',
                Rule::unique('products', 'sku')->ignore($product->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'stock' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['sku'] = strtoupper(trim($validated['sku']));
        $validated['is_active'] = $request->boolean('is_active');

        $product->update($validated);

        return redirect()
            ->route('products.index')
            ->with('status', "Data produk {$product->sku} berhasil diperbarui.");
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $sku = $product->sku;
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('status', "Produk {$sku} telah dihapus dari Master Data.");
    }
}

