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

class ChaosTransactionTest extends TestCase
{
    use RefreshDatabase;

    private function createSetup(int $initialStock = 5): array
    {
        $admin = User::query()->create([
            'name' => 'Chaos Tester Admin',
            'email' => 'chaos_admin@fahdims.test',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $category = Category::query()->create([
            'name' => 'Hardware Uji Chaos',
            'slug' => 'hardware-uji-chaos',
            'description' => 'Kategori pengujian stres dan input ekstrem.',
        ]);

        $unit = Unit::query()->create([
            'name' => 'Unit',
            'symbol' => 'unit',
        ]);

        $supplier = Supplier::query()->create([
            'name' => 'PT Chaos Resilience',
            'contact_person' => 'Rizky Pratama',
            'phone' => '021-5550999',
            'email' => 'qa@chaosresilience.test',
            'address' => 'Jakarta',
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'supplier_id' => $supplier->id,
            'sku' => 'PRD-CHS-001',
            'name' => 'Firewall Appliance X100',
            'stock' => $initialStock,
            'minimum_stock' => 5,
            'purchase_price' => 5000000,
            'selling_price' => 6000000,
            'is_active' => true,
        ]);

        return [$admin, $product];
    }

    public function test_scenario_1_negative_and_zero_quantities_are_rejected(): void
    {
        [$admin, $product] = $this->createSetup(5);

        // 1a. Stock Out dengan quantity = -50
        $resOutNeg = $this->actingAs($admin)
            ->from(route('transactions.create'))
            ->post(route('transactions.store'), [
                'product_id' => $product->id,
                'type' => StockMutation::TYPE_OUT,
                'quantity' => -50,
            ]);
        $resOutNeg->assertStatus(302);
        $resOutNeg->assertSessionHasErrors('quantity');

        // 1b. Stock In dengan quantity = -50
        $resInNeg = $this->actingAs($admin)
            ->from(route('transactions.create'))
            ->post(route('transactions.store'), [
                'product_id' => $product->id,
                'type' => StockMutation::TYPE_IN,
                'quantity' => -50,
            ]);
        $resInNeg->assertStatus(302);
        $resInNeg->assertSessionHasErrors('quantity');

        // 1c. Stock In/Out dengan quantity = 0
        $resZero = $this->actingAs($admin)
            ->from(route('transactions.create'))
            ->post(route('transactions.store'), [
                'product_id' => $product->id,
                'type' => StockMutation::TYPE_IN,
                'quantity' => 0,
            ]);
        $resZero->assertStatus(302);
        $resZero->assertSessionHasErrors('quantity');

        $this->assertSame(5, $product->fresh()->stock);
        $this->assertDatabaseCount('stock_mutations', 0);
    }

    public function test_scenario_2_fuzzing_invalid_data_types_and_xss_payload(): void
    {
        [$admin, $product] = $this->createSetup(5);

        // 2a. quantity = "seratus" (string huruf)
        $resString = $this->actingAs($admin)
            ->from(route('transactions.create'))
            ->post(route('transactions.store'), [
                'product_id' => $product->id,
                'type' => StockMutation::TYPE_IN,
                'quantity' => 'seratus',
            ]);
        $resString->assertStatus(302);
        $resString->assertSessionHasErrors('quantity');

        // 2b. quantity = 12.75 (angka desimal)
        $resFloat = $this->actingAs($admin)
            ->from(route('transactions.create'))
            ->post(route('transactions.store'), [
                'product_id' => $product->id,
                'type' => StockMutation::TYPE_IN,
                'quantity' => '12.75',
            ]);
        $resFloat->assertStatus(302);
        $resFloat->assertSessionHasErrors('quantity');

        // 2c. quantity = 99999999999999999999 (integer overflow)
        $resOverflow = $this->actingAs($admin)
            ->from(route('transactions.create'))
            ->post(route('transactions.store'), [
                'product_id' => $product->id,
                'type' => StockMutation::TYPE_IN,
                'quantity' => '99999999999999999999',
            ]);
        $resOverflow->assertStatus(302);
        $resOverflow->assertSessionHasErrors('quantity');

        // 2d. Payload kosong sepenuhnya
        $resEmpty = $this->actingAs($admin)
            ->from(route('transactions.create'))
            ->post(route('transactions.store'), []);
        $resEmpty->assertStatus(302);
        $resEmpty->assertSessionHasErrors(['product_id', 'type', 'quantity']);

        // Pastikan seluruh fuzzing gagal mengubah stok
        $this->assertSame(5, $product->fresh()->stock);
        $this->assertDatabaseCount('stock_mutations', 0);

        // 2e. XSS Payload pada notes: disimpan aman via prepared statement dan di-escape oleh Blade
        $xssPayload = "<script>alert('hack')</script>";
        $resXss = $this->actingAs($admin)
            ->post(route('transactions.store'), [
                'product_id' => $product->id,
                'type' => StockMutation::TYPE_IN,
                'quantity' => 2,
                'notes' => $xssPayload,
            ]);
        $resXss->assertStatus(302);
        $resXss->assertSessionHasNoErrors();

        $indexHtml = $this->actingAs($admin)->get(route('transactions.index'))->getContent();
        $this->assertStringNotContainsString("<script>alert('hack')</script>", $indexHtml);
        $this->assertStringContainsString('&lt;script&gt;alert(&#039;hack&#039;)&lt;/script&gt;', $indexHtml);
    }

    public function test_scenario_3_overdraft_attack_on_stock_5_is_blocked(): void
    {
        [$admin, $product] = $this->createSetup(5);

        // 3a. Stock Out quantity = 6 (stok tersedia = 5)
        $resOut6 = $this->actingAs($admin)
            ->from(route('transactions.create'))
            ->post(route('transactions.store'), [
                'product_id' => $product->id,
                'type' => StockMutation::TYPE_OUT,
                'quantity' => 6,
            ]);
        $resOut6->assertStatus(302);
        $resOut6->assertSessionHasErrors('quantity');
        $this->assertSame(5, $product->fresh()->stock);

        // 3b. Stock Out quantity = 999 (stok tersedia = 5)
        $resOut999 = $this->actingAs($admin)
            ->from(route('transactions.create'))
            ->post(route('transactions.store'), [
                'product_id' => $product->id,
                'type' => StockMutation::TYPE_OUT,
                'quantity' => 999,
            ]);
        $resOut999->assertStatus(302);
        $resOut999->assertSessionHasErrors('quantity');
        $this->assertSame(5, $product->fresh()->stock);
        $this->assertDatabaseCount('stock_mutations', 0);
    }

    public function test_scenario_4_ghost_product_id_and_invalid_type_are_rejected(): void
    {
        [$admin, $product] = $this->createSetup(5);

        // 4a. product_id = 99999 (Ghost ID)
        $resGhostProduct = $this->actingAs($admin)
            ->from(route('transactions.create'))
            ->post(route('transactions.store'), [
                'product_id' => 99999,
                'type' => StockMutation::TYPE_IN,
                'quantity' => 10,
            ]);
        $resGhostProduct->assertStatus(302);
        $resGhostProduct->assertSessionHasErrors('product_id');

        // 4b. type = "hack_type"
        $resInvalidType = $this->actingAs($admin)
            ->from(route('transactions.create'))
            ->post(route('transactions.store'), [
                'product_id' => $product->id,
                'type' => 'hack_type',
                'quantity' => 10,
            ]);
        $resInvalidType->assertStatus(302);
        $resInvalidType->assertSessionHasErrors('type');

        $this->assertSame(5, $product->fresh()->stock);
        $this->assertDatabaseCount('stock_mutations', 0);
    }

    public function test_scenario_5_sequential_normal_transactions_maintain_exact_snapshots(): void
    {
        [$admin, $product] = $this->createSetup(5);

        // Step 1: Stock In 10 unit (5 -> 15)
        $this->actingAs($admin)->post(route('transactions.store'), [
            'product_id' => $product->id,
            'type' => StockMutation::TYPE_IN,
            'quantity' => 10,
            'notes' => 'Sequential Step 1: Inbound +10',
        ])->assertSessionHasNoErrors();
        $this->assertSame(15, $product->fresh()->stock);

        // Step 2: Stock Out 4 unit (15 -> 11)
        $this->actingAs($admin)->post(route('transactions.store'), [
            'product_id' => $product->id,
            'type' => StockMutation::TYPE_OUT,
            'quantity' => 4,
            'notes' => 'Sequential Step 2: Outbound -4',
        ])->assertSessionHasNoErrors();
        $this->assertSame(11, $product->fresh()->stock);

        // Step 3: Adjustment stok fisik menjadi 20 (11 -> 20, delta +9)
        $this->actingAs($admin)->post(route('transactions.store'), [
            'product_id' => $product->id,
            'type' => StockMutation::TYPE_ADJUSTMENT,
            'quantity' => 20,
            'notes' => 'Sequential Step 3: Adjustment to 20',
        ])->assertSessionHasNoErrors();
        $this->assertSame(20, $product->fresh()->stock);

        $mutations = StockMutation::query()
            ->where('product_id', $product->id)
            ->orderBy('id')
            ->get();

        $this->assertCount(3, $mutations);

        $this->assertSame(5, $mutations[0]->previous_stock);
        $this->assertSame(15, $mutations[0]->current_stock);
        $this->assertSame(10, $mutations[0]->quantity);

        $this->assertSame(15, $mutations[1]->previous_stock);
        $this->assertSame(11, $mutations[1]->current_stock);
        $this->assertSame(4, $mutations[1]->quantity);

        $this->assertSame(11, $mutations[2]->previous_stock);
        $this->assertSame(20, $mutations[2]->current_stock);
        $this->assertSame(9, $mutations[2]->quantity);
    }
}

