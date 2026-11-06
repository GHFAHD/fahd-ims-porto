<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMutation;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private function seedReportFixture(): array
    {
        $admin = User::query()->create([
            'name' => 'Fahd Administrator',
            'email' => 'admin@fahdims.test',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $category = Category::query()->create([
            'name' => 'Peralatan Jaringan',
            'slug' => 'peralatan-jaringan',
            'description' => 'Perangkat jaringan enterprise.',
        ]);

        $unit = Unit::query()->create([
            'name' => 'Unit',
            'symbol' => 'unit',
        ]);

        $supplier = Supplier::query()->create([
            'name' => 'PT Sinergi Jaringan',
            'contact_person' => 'Hendra Wijaya',
            'phone' => '021-5550199',
            'email' => 'info@sinergijaringan.co.id',
            'address' => 'Jakarta Selatan',
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'supplier_id' => $supplier->id,
            'sku' => 'PRD-NET-901',
            'name' => 'Core Switch 24-Port SFP+',
            'stock' => 12,
            'minimum_stock' => 4,
            'purchase_price' => 8500000,
            'selling_price' => 9800000,
            'is_active' => true,
        ]);

        $mutation = StockMutation::query()->create([
            'reference_number' => 'IN-20261106-TEST',
            'product_id' => $product->id,
            'user_id' => $admin->id,
            'type' => StockMutation::TYPE_IN,
            'quantity' => 12,
            'previous_stock' => 0,
            'current_stock' => 12,
            'notes' => 'Pengadaan awal switch core.',
        ]);

        return [$admin, $category, $product, $mutation];
    }

    public function test_reports_endpoint_is_accessible_and_date_range_filter_works(): void
    {
        [$admin, $category, $product, $mutation] = $this->seedReportFixture();

        $response = $this->actingAs($admin)->get(route('reports.index', [
            'start_date' => now()->subDays(7)->toDateString(),
            'end_date' => now()->toDateString(),
            'type' => StockMutation::TYPE_IN,
            'category_id' => $category->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee($mutation->reference_number);
        $response->assertSee($product->sku);
        $response->assertSee($category->name);
    }

    public function test_reports_export_returns_csv_streamed_response_with_status_200(): void
    {
        [$admin, $category, $product, $mutation] = $this->seedReportFixture();

        $response = $this->actingAs($admin)->get(route('reports.export', [
            'start_date' => now()->subDays(7)->toDateString(),
            'end_date' => now()->toDateString(),
            'type' => StockMutation::TYPE_IN,
            'category_id' => $category->id,
        ]));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString(
            'attachment; filename=laporan-mutasi-stok_',
            (string) $response->headers->get('Content-Disposition')
        );

        $csvContent = $response->streamedContent();
        $this->assertStringContainsString('No Referensi', $csvContent);
        $this->assertStringContainsString($mutation->reference_number, $csvContent);
        $this->assertStringContainsString($product->sku, $csvContent);
    }
}

