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

class StockMutationTest extends TestCase
{
    use RefreshDatabase;

    private function createActiveUser(string $role = 'admin'): User
    {
        return User::query()->create([
            'name' => ucfirst($role) . ' Tester',
            'email' => $role . '_' . uniqid() . '@fahdims.test',
            'password' => 'password',
            'role' => $role,
            'is_active' => true,
        ]);
    }

    private function createProduct(int $initialStock = 20): Product
    {
        $category = Category::query()->create([
            'name' => 'Komponen Server',
            'slug' => 'komponen-server-' . uniqid(),
            'description' => 'Kategori pengujian komponen server.',
        ]);

        $unit = Unit::query()->create([
            'name' => 'Unit',
            'symbol' => 'unit_' . uniqid(),
        ]);

        $supplier = Supplier::query()->create([
            'name' => 'PT Nusantara Distribusi',
            'contact_person' => 'Budi Santoso',
            'phone' => '021-5550101',
            'email' => 'sales_' . uniqid() . '@nusantara.co.id',
            'address' => 'Jakarta',
        ]);

        return Product::query()->create([
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'supplier_id' => $supplier->id,
            'sku' => 'PRD-TST-' . strtoupper(substr(uniqid(), -4)),
            'name' => 'Processor Server Xeon',
            'stock' => $initialStock,
            'minimum_stock' => 5,
            'purchase_price' => 4500000,
            'selling_price' => 5250000,
            'is_active' => true,
        ]);
    }

    public function test_stock_in_increments_product_stock_and_records_mutation_log(): void
    {
        $admin = $this->createActiveUser('admin');
        $product = $this->createProduct(20);

        $response = $this->actingAs($admin)->post(route('transactions.store'), [
            'product_id' => $product->id,
            'type' => StockMutation::TYPE_IN,
            'quantity' => 15,
            'notes' => 'Penerimaan PO batch pengujian.',
        ]);

        $response->assertRedirect(route('transactions.index'));
        $response->assertSessionHasNoErrors();

        $this->assertSame(35, $product->fresh()->stock);

        $this->assertDatabaseHas('stock_mutations', [
            'product_id' => $product->id,
            'user_id' => $admin->id,
            'type' => StockMutation::TYPE_IN,
            'quantity' => 15,
            'previous_stock' => 20,
            'current_stock' => 35,
            'notes' => 'Penerimaan PO batch pengujian.',
        ]);
    }

    public function test_stock_out_decrements_product_stock_when_balance_is_sufficient(): void
    {
        $admin = $this->createActiveUser('admin');
        $product = $this->createProduct(20);

        $response = $this->actingAs($admin)->post(route('transactions.store'), [
            'product_id' => $product->id,
            'type' => StockMutation::TYPE_OUT,
            'quantity' => 8,
            'notes' => 'Pengeluaran stok untuk instalasi rak server.',
        ]);

        $response->assertRedirect(route('transactions.index'));
        $response->assertSessionHasNoErrors();

        $this->assertSame(12, $product->fresh()->stock);

        $this->assertDatabaseHas('stock_mutations', [
            'product_id' => $product->id,
            'user_id' => $admin->id,
            'type' => StockMutation::TYPE_OUT,
            'quantity' => 8,
            'previous_stock' => 20,
            'current_stock' => 12,
        ]);
    }

    public function test_stock_out_is_rejected_when_quantity_exceeds_available_stock(): void
    {
        $admin = $this->createActiveUser('admin');
        $product = $this->createProduct(10);

        $response = $this->actingAs($admin)
            ->from(route('transactions.create'))
            ->post(route('transactions.store'), [
                'product_id' => $product->id,
                'type' => StockMutation::TYPE_OUT,
                'quantity' => 25,
                'notes' => 'Percobaan pengeluaran melebihi saldo stok.',
            ]);

        $response->assertRedirect(route('transactions.create'));
        $response->assertSessionHasErrors('quantity');

        // Pastikan saldo stok produk tetap utuh (tidak berubah / tidak negatif)
        $this->assertSame(10, $product->fresh()->stock);

        // Pastikan tidak ada log mutasi yang tersimpan
        $this->assertDatabaseCount('stock_mutations', 0);
    }

    public function test_staff_and_admin_roles_can_record_stock_transactions(): void
    {
        $staff = $this->createActiveUser('staff');
        $admin = $this->createActiveUser('admin');
        $product = $this->createProduct(10);

        // 1. Staff mencatat Stock In (+5 -> 15)
        $staffResponse = $this->actingAs($staff)->post(route('transactions.store'), [
            'product_id' => $product->id,
            'type' => StockMutation::TYPE_IN,
            'quantity' => 5,
            'notes' => 'Dicatat oleh petugas Staff.',
        ]);

        $staffResponse->assertRedirect(route('transactions.index'));
        $this->assertSame(15, $product->fresh()->stock);

        // 2. Admin mencatat Stock Adjustment (opname fisik -> 18)
        $adminResponse = $this->actingAs($admin)->post(route('transactions.store'), [
            'product_id' => $product->id,
            'type' => StockMutation::TYPE_ADJUSTMENT,
            'quantity' => 18,
            'notes' => 'Penyesuaian stok opname oleh Admin.',
        ]);

        $adminResponse->assertRedirect(route('transactions.index'));
        $this->assertSame(18, $product->fresh()->stock);
        $this->assertDatabaseCount('stock_mutations', 2);
    }
}

