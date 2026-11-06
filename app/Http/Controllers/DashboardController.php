<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMutation;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the operational dashboard with real-time database aggregations and activity feeds.
     */
    public function index(): View
    {
        $totalProducts = Product::query()->count();

        $lowStockCount = Product::query()
            ->whereColumn('stock', '<=', 'minimum_stock')
            ->count();

        $stockInToday = (int) StockMutation::query()
            ->where('type', StockMutation::TYPE_IN)
            ->whereDate('created_at', today())
            ->sum('quantity');

        $stockOutToday = (int) StockMutation::query()
            ->where('type', StockMutation::TYPE_OUT)
            ->whereDate('created_at', today())
            ->sum('quantity');

        $recentMutations = StockMutation::query()
            ->with(['product.unit', 'user'])
            ->latest('id')
            ->take(5)
            ->get();

        $lowStockProducts = Product::query()
            ->with(['category', 'unit', 'supplier'])
            ->whereColumn('stock', '<=', 'minimum_stock')
            ->orderBy('stock')
            ->take(5)
            ->get();

        $categoriesCount = Category::query()->count();

        return view('dashboard', [
            'totalProducts' => $totalProducts,
            'lowStockCount' => $lowStockCount,
            'stockInToday' => $stockInToday,
            'stockOutToday' => $stockOutToday,
            'recentMutations' => $recentMutations,
            'lowStockProducts' => $lowStockProducts,
            'categoriesCount' => $categoriesCount,
        ]);
    }
}

