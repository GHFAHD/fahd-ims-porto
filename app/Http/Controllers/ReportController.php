<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\StockMutation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Display the inventory mutation report with date-range filters and period summary.
     */
    public function index(Request $request): View
    {
        $filters = $this->resolveFilters($request);
        $baseQuery = $this->buildFilteredQuery($filters);

        $summary = [
            'total_transactions' => (clone $baseQuery)->count(),
            'total_units_in' => (int) (clone $baseQuery)
                ->where('type', StockMutation::TYPE_IN)
                ->sum('quantity'),
            'total_units_out' => (int) (clone $baseQuery)
                ->where('type', StockMutation::TYPE_OUT)
                ->sum('quantity'),
            'total_adjustments' => (clone $baseQuery)
                ->where('type', StockMutation::TYPE_ADJUSTMENT)
                ->count(),
        ];

        $mutations = (clone $baseQuery)
            ->with(['product.category', 'product.unit', 'user'])
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $categories = Category::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $selectedCategory = $filters['category_id']
            ? $categories->firstWhere('id', (int) $filters['category_id'])
            : null;

        return view('reports.index', [
            'mutations' => $mutations,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'filters' => $filters,
            'summary' => $summary,
        ]);
    }

    /**
     * Stream an Excel-compatible UTF-8 CSV export for the filtered stock mutations.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $filters = $this->resolveFilters($request);
        $query = $this->buildFilteredQuery($filters)
            ->with(['product.category', 'product.unit', 'user'])
            ->latest('id');

        $filename = sprintf(
            'laporan-mutasi-stok_%s_sd_%s.csv',
            $filters['start_date'],
            $filters['end_date']
        );

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            // Write UTF-8 BOM for automatic Microsoft Excel UTF-8 column detection
            fwrite($handle, "\xEF\xBB\xBF");

            // CSV Header Columns
            fputcsv($handle, [
                'Tanggal',
                'No Referensi',
                'Tipe',
                'SKU',
                'Nama Produk',
                'Kategori',
                'Qty',
                'Stok Awal',
                'Stok Akhir',
                'Petugas',
                'Catatan',
            ]);

            $query->chunk(200, function ($mutations) use ($handle): void {
                foreach ($mutations as $mutation) {
                    $delta = $mutation->signedDelta();
                    $formattedQty = $delta > 0
                        ? '+' . $mutation->quantity
                        : ($delta < 0 ? '-' . $mutation->quantity : '0');

                    fputcsv($handle, [
                        $mutation->created_at?->format('Y-m-d H:i:s') ?? '-',
                        $mutation->reference_number,
                        $mutation->typeLabel(),
                        $mutation->product?->sku ?? '-',
                        $mutation->product?->name ?? 'Produk Terhapus',
                        $mutation->product?->category?->name ?? '-',
                        $formattedQty,
                        $mutation->previous_stock,
                        $mutation->current_stock,
                        $mutation->user?->name ?? 'System',
                        $mutation->notes ?? '',
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    /**
     * Normalize and validate report filter query parameters.
     *
     * @return array{start_date: string, end_date: string, type: string, category_id: int|null}
     */
    private function resolveFilters(Request $request): array
    {
        $defaultStart = now()->subDays(30)->toDateString();
        $defaultEnd = now()->toDateString();

        $startDate = $this->parseDateOrFallback((string) $request->query('start_date', ''), $defaultStart);
        $endDate = $this->parseDateOrFallback((string) $request->query('end_date', ''), $defaultEnd);

        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $type = (string) $request->query('type', '');
        if (! in_array($type, [StockMutation::TYPE_IN, StockMutation::TYPE_OUT, StockMutation::TYPE_ADJUSTMENT], true)) {
            $type = '';
        }

        $rawCategoryId = $request->query('category_id');
        $categoryId = is_numeric($rawCategoryId) && (int) $rawCategoryId > 0
            ? (int) $rawCategoryId
            : null;

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'type' => $type,
            'category_id' => $categoryId,
        ];
    }

    /**
     * Build the base Eloquent query for StockMutation using the normalized report filters.
     *
     * @param  array{start_date: string, end_date: string, type: string, category_id: int|null}  $filters
     * @return Builder<StockMutation>
     */
    private function buildFilteredQuery(array $filters): Builder
    {
        return StockMutation::query()
            ->whereDate('created_at', '>=', $filters['start_date'])
            ->whereDate('created_at', '<=', $filters['end_date'])
            ->when($filters['type'] !== '', function (Builder $query) use ($filters): void {
                $query->where('type', $filters['type']);
            })
            ->when($filters['category_id'] !== null, function (Builder $query) use ($filters): void {
                $query->whereHas('product', function (Builder $productQuery) use ($filters): void {
                    $productQuery->where('category_id', $filters['category_id']);
                });
            });
    }

    /**
     * Parse a Y-m-d date string safely or return the fallback date string.
     */
    private function parseDateOrFallback(string $value, string $fallback): string
    {
        if (trim($value) === '') {
            return $fallback;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', trim($value))->toDateString();
        } catch (\Throwable) {
            return $fallback;
        }
    }
}

