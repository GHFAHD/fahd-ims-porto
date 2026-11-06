<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMutation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StockMutationController extends Controller
{
    /**
     * Display a paginated history of stock mutations with filter support.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $type = $request->query('type');
        $productId = $request->query('product_id');

        $mutationsQuery = StockMutation::query()
            ->with(['product.unit', 'product.category', 'user'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('reference_number', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('product', function ($productQuery) use ($search): void {
                            $productQuery->where('sku', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when(in_array($type, [
                StockMutation::TYPE_IN,
                StockMutation::TYPE_OUT,
                StockMutation::TYPE_ADJUSTMENT,
            ], true), function ($query) use ($type): void {
                $query->where('type', $type);
            })
            ->when($productId, function ($query) use ($productId): void {
                $query->where('product_id', $productId);
            })
            ->latest('id');

        $mutations = $mutationsQuery->paginate(12)->withQueryString();

        $today = now()->toDateString();

        $totalInToday = (int) StockMutation::query()
            ->where('type', StockMutation::TYPE_IN)
            ->whereDate('created_at', $today)
            ->sum('quantity');

        $totalOutToday = (int) StockMutation::query()
            ->where('type', StockMutation::TYPE_OUT)
            ->whereDate('created_at', $today)
            ->sum('quantity');

        $totalAdjustmentsCount = StockMutation::query()
            ->where('type', StockMutation::TYPE_ADJUSTMENT)
            ->count();

        $products = Product::query()->with('unit')->orderBy('sku')->get();

        return view('transactions.index', [
            'mutations' => $mutations,
            'products' => $products,
            'totalMutationsCount' => StockMutation::query()->count(),
            'totalInToday' => $totalInToday,
            'totalOutToday' => $totalOutToday,
            'totalAdjustmentsCount' => $totalAdjustmentsCount,
            'filters' => [
                'search' => $search,
                'type' => $type,
                'product_id' => $productId,
            ],
        ]);
    }

    /**
     * Show the form for recording a new stock mutation (in, out, or adjustment).
     */
    public function create(Request $request): View
    {
        $products = Product::query()
            ->with(['unit', 'category', 'supplier'])
            ->where('is_active', true)
            ->orderBy('sku')
            ->get();

        $selectedProductId = $request->query('product_id');
        $selectedType = $request->query('type', StockMutation::TYPE_IN);

        if (! in_array($selectedType, [
            StockMutation::TYPE_IN,
            StockMutation::TYPE_OUT,
            StockMutation::TYPE_ADJUSTMENT,
        ], true)) {
            $selectedType = StockMutation::TYPE_IN;
        }

        return view('transactions.create', [
            'products' => $products,
            'selectedProductId' => $selectedProductId,
            'selectedType' => $selectedType,
        ]);
    }

    /**
     * Execute and store a stock mutation atomically inside a database transaction.
     */
    public function store(Request $request): RedirectResponse
    {
        $type = (string) $request->input('type', '');

        $validated = $request->validate(
            [
                'product_id' => ['required', 'integer', 'exists:products,id'],
                'type' => [
                    'required',
                    'string',
                    Rule::in([
                        StockMutation::TYPE_IN,
                        StockMutation::TYPE_OUT,
                        StockMutation::TYPE_ADJUSTMENT,
                    ]),
                ],
                'quantity' => [
                    'required',
                    'integer',
                    $type === StockMutation::TYPE_ADJUSTMENT ? 'min:0' : 'min:1',
                ],
                'notes' => ['nullable', 'string', 'max:1000'],
            ],
            [
                'product_id.required' => 'Silakan pilih produk terlebih dahulu.',
                'product_id.exists' => 'Produk yang dipilih tidak ditemukan dalam Master Data.',
                'type.required' => 'Tipe transaksi wajib dipilih.',
                'type.in' => 'Tipe transaksi tidak valid.',
                'quantity.required' => 'Kuantitas atau nilai stok wajib diisi.',
                'quantity.integer' => 'Kuantitas harus berupa angka bulat.',
                'quantity.min' => $type === StockMutation::TYPE_ADJUSTMENT
                    ? 'Nilai stok fisik penyesuaian tidak boleh bernilai negatif (minimal 0).'
                    : 'Kuantitas mutasi masuk/keluar minimal adalah 1.',
            ]
        );

        /** @var StockMutation $mutation */
        $mutation = DB::transaction(function () use ($validated, $request): StockMutation {
            /** @var Product $product */
            $product = Product::query()
                ->with('unit')
                ->whereKey($validated['product_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $previousStock = (int) $product->stock;
            $inputQty = (int) $validated['quantity'];
            $unitSymbol = $product->unit?->symbol ?? 'unit';

            if ($validated['type'] === StockMutation::TYPE_IN) {
                $currentStock = $previousStock + $inputQty;
                $recordedQuantity = $inputQty;
            } elseif ($validated['type'] === StockMutation::TYPE_OUT) {
                if ($inputQty > $previousStock) {
                    throw ValidationException::withMessages([
                        'quantity' => "Stok produk {$product->sku} ({$product->name}) tidak mencukupi. Stok tersedia saat ini: {$previousStock} {$unitSymbol}, sedangkan permintaan keluar: {$inputQty} {$unitSymbol}.",
                    ]);
                }

                $currentStock = $previousStock - $inputQty;
                $recordedQuantity = $inputQty;
            } else {
                // Adjustment: inputQty represents the new physical stock count (>= 0)
                $currentStock = $inputQty;
                $recordedQuantity = abs($currentStock - $previousStock);
            }

            $mutation = StockMutation::query()->create([
                'reference_number' => StockMutation::generateReferenceNumber($validated['type']),
                'product_id' => $product->id,
                'user_id' => (int) $request->user()->id,
                'type' => $validated['type'],
                'quantity' => $recordedQuantity,
                'previous_stock' => $previousStock,
                'current_stock' => $currentStock,
                'notes' => $validated['notes'] ?? null,
            ]);

            $product->update([
                'stock' => $currentStock,
            ]);

            return $mutation;
        });

        return redirect()
            ->route('transactions.index')
            ->with(
                'status',
                "Transaksi {$mutation->reference_number} ({$mutation->typeLabel()}) berhasil dicatat. Stok produk diperbarui dari {$mutation->previous_stock} menjadi {$mutation->current_stock}."
            );
    }
}

