<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\StockMutation;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StockMutationSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed realistic stock mutation history consistent with MasterDataSeeder product stocks.
     */
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@fahdims.test')->first();
        $staff = User::query()->where('email', 'staff@fahdims.test')->first();

        if (! $admin) {
            return;
        }

        $staffUser = $staff ?? $admin;
        $products = Product::query()->get()->keyBy('sku');

        $datePart = now()->format('Ymd');

        $records = [
            [
                'reference_number' => "IN-{$datePart}-A101",
                'sku' => 'PRD-CPU-001',
                'user_id' => $admin->id,
                'type' => StockMutation::TYPE_IN,
                'quantity' => 20,
                'previous_stock' => 0,
                'current_stock' => 20,
                'notes' => 'Penerimaan awal PO-2026-101 dari PT Nusantara Teknologi Distribusi.',
            ],
            [
                'reference_number' => "OUT-{$datePart}-B201",
                'sku' => 'PRD-CPU-001',
                'user_id' => $staffUser->id,
                'type' => StockMutation::TYPE_OUT,
                'quantity' => 2,
                'previous_stock' => 20,
                'current_stock' => 18,
                'notes' => 'Pengeluaran barang SJ-2026-041 untuk perakitan workstation.',
            ],
            [
                'reference_number' => "IN-{$datePart}-A102",
                'sku' => 'PRD-RAM-002',
                'user_id' => $admin->id,
                'type' => StockMutation::TYPE_IN,
                'quantity' => 10,
                'previous_stock' => 0,
                'current_stock' => 10,
                'notes' => 'Penerimaan PO-2026-102 modul RAM DDR5 dari distributor.',
            ],
            [
                'reference_number' => "OUT-{$datePart}-B202",
                'sku' => 'PRD-RAM-002',
                'user_id' => $staffUser->id,
                'type' => StockMutation::TYPE_OUT,
                'quantity' => 6,
                'previous_stock' => 10,
                'current_stock' => 4,
                'notes' => 'Pengiriman pesanan klien korporat SJ-2026-042.',
            ],
            [
                'reference_number' => "IN-{$datePart}-A103",
                'sku' => 'PRD-SSD-003',
                'user_id' => $admin->id,
                'type' => StockMutation::TYPE_IN,
                'quantity' => 25,
                'previous_stock' => 0,
                'current_stock' => 25,
                'notes' => 'Stok masuk PO-2026-103 dari CV Mitra Komputindo Utama.',
            ],
            [
                'reference_number' => "ADJ-{$datePart}-C301",
                'sku' => 'PRD-SSD-003',
                'user_id' => $admin->id,
                'type' => StockMutation::TYPE_ADJUSTMENT,
                'quantity' => 1,
                'previous_stock' => 25,
                'current_stock' => 24,
                'notes' => 'Penyesuaian Stock Opname bulanan (1 unit dialokasikan untuk uji QC).',
            ],
            [
                'reference_number' => "IN-{$datePart}-A104",
                'sku' => 'PRD-NET-004',
                'user_id' => $admin->id,
                'type' => StockMutation::TYPE_IN,
                'quantity' => 8,
                'previous_stock' => 0,
                'current_stock' => 8,
                'notes' => 'Penerimaan perangkat router MikroTik PO-2026-104.',
            ],
            [
                'reference_number' => "OUT-{$datePart}-B203",
                'sku' => 'PRD-NET-004',
                'user_id' => $staffUser->id,
                'type' => StockMutation::TYPE_OUT,
                'quantity' => 5,
                'previous_stock' => 8,
                'current_stock' => 3,
                'notes' => 'Instalasi infrastruktur jaringan cabang SJ-2026-045.',
            ],
        ];

        foreach ($records as $item) {
            $product = $products->get($item['sku']);
            if (! $product) {
                continue;
            }

            StockMutation::updateOrCreate(
                ['reference_number' => $item['reference_number']],
                [
                    'product_id' => $product->id,
                    'user_id' => $item['user_id'],
                    'type' => $item['type'],
                    'quantity' => $item['quantity'],
                    'previous_stock' => $item['previous_stock'],
                    'current_stock' => $item['current_stock'],
                    'notes' => $item['notes'],
                ]
            );
        }
    }
}

