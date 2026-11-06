<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed realistic master data for Categories, Units, Suppliers, and Products.
     */
    public function run(): void
    {
        // 1. Seed Categories (4 Categories)
        $categories = [
            'komponen-komputer' => Category::updateOrCreate(
                ['slug' => 'komponen-komputer'],
                [
                    'name' => 'Komponen Komputer',
                    'description' => 'Prosesor, modul memori RAM, motherboard, dan kartu grafis.',
                ]
            ),
            'aksesoris-elektronik' => Category::updateOrCreate(
                ['slug' => 'aksesoris-elektronik'],
                [
                    'name' => 'Aksesoris Elektronik',
                    'description' => 'Periferal input/output seperti mouse, keyboard mekanik, dan adaptor.',
                ]
            ),
            'peralatan-jaringan' => Category::updateOrCreate(
                ['slug' => 'peralatan-jaringan'],
                [
                    'name' => 'Peralatan Jaringan',
                    'description' => 'Router enterprise, switch managed, access point, dan kabel instalasi UTP.',
                ]
            ),
            'perangkat-penyimpanan' => Category::updateOrCreate(
                ['slug' => 'perangkat-penyimpanan'],
                [
                    'name' => 'Perangkat Penyimpanan',
                    'description' => 'Media penyimpanan SSD NVMe, SATA, dan HDD enterprise NAS.',
                ]
            ),
        ];

        // 2. Seed Units (4 Units)
        $units = [
            'pcs' => Unit::updateOrCreate(['symbol' => 'pcs'], ['name' => 'Pieces']),
            'box' => Unit::updateOrCreate(['symbol' => 'box'], ['name' => 'Box']),
            'pack' => Unit::updateOrCreate(['symbol' => 'pack'], ['name' => 'Pack']),
            'unit' => Unit::updateOrCreate(['symbol' => 'unit'], ['name' => 'Unit']),
        ];

        // 3. Seed Suppliers (3 Official Suppliers)
        $suppliers = [
            'nusantara' => Supplier::updateOrCreate(
                ['email' => 'procurement@nusantaratek.co.id'],
                [
                    'name' => 'PT Nusantara Teknologi Distribusi',
                    'contact_name' => 'Hendra Wijaya',
                    'phone' => '+62-21-5580-1120',
                    'address' => 'Kawasan Industri Pulogadung Blok J-12, Jakarta Timur',
                ]
            ),
            'mitra' => Supplier::updateOrCreate(
                ['email' => 'sales@mitrakomputindo.id'],
                [
                    'name' => 'CV Mitra Komputindo Utama',
                    'contact_name' => 'Rina Kartika',
                    'phone' => '+62-22-4209-8831',
                    'address' => 'Jl. Ahmad Yani No. 248, Bandung',
                ]
            ),
            'globalnet' => Supplier::updateOrCreate(
                ['email' => 'b2b@globaljaringan.co.id'],
                [
                    'name' => 'PT Global Jaringan Solusindo',
                    'contact_name' => 'Budi Santoso',
                    'phone' => '+62-31-8432-7700',
                    'address' => 'Rungkut Industri Raya No. 45, Surabaya',
                ]
            ),
        ];

        // 4. Seed Products (6 Products: 3 Normal Stock, 3 Low Stock)
        $products = [
            [
                'sku' => 'PRD-CPU-001',
                'name' => 'Processor AMD Ryzen 7 7700X Box',
                'category_id' => $categories['komponen-komputer']->id,
                'unit_id' => $units['unit']->id,
                'supplier_id' => $suppliers['nusantara']->id,
                'stock' => 18,
                'minimum_stock' => 5,
                'purchase_price' => 4850000.00,
                'selling_price' => 5390000.00,
                'is_active' => true,
            ],
            [
                'sku' => 'PRD-RAM-002',
                'name' => 'RAM Corsair Vengeance DDR5 32GB (2x16GB) 6000MHz',
                'category_id' => $categories['komponen-komputer']->id,
                'unit_id' => $units['pack']->id,
                'supplier_id' => $suppliers['nusantara']->id,
                'stock' => 4, // Low stock (<= 6)
                'minimum_stock' => 6,
                'purchase_price' => 1680000.00,
                'selling_price' => 1950000.00,
                'is_active' => true,
            ],
            [
                'sku' => 'PRD-SSD-003',
                'name' => 'SSD Samsung 990 PRO NVMe M.2 PCIe 4.0 1TB',
                'category_id' => $categories['perangkat-penyimpanan']->id,
                'unit_id' => $units['pcs']->id,
                'supplier_id' => $suppliers['mitra']->id,
                'stock' => 24,
                'minimum_stock' => 8,
                'purchase_price' => 1520000.00,
                'selling_price' => 1790000.00,
                'is_active' => true,
            ],
            [
                'sku' => 'PRD-NET-004',
                'name' => 'MikroTik RouterBOARD hAP ax3 Dual-Band Wi-Fi 6',
                'category_id' => $categories['peralatan-jaringan']->id,
                'unit_id' => $units['unit']->id,
                'supplier_id' => $suppliers['globalnet']->id,
                'stock' => 3, // Low stock (<= 5)
                'minimum_stock' => 5,
                'purchase_price' => 2050000.00,
                'selling_price' => 2350000.00,
                'is_active' => true,
            ],
            [
                'sku' => 'PRD-NET-005',
                'name' => 'Kabel UTP Belden Cat6 Original (Roll 305m)',
                'category_id' => $categories['peralatan-jaringan']->id,
                'unit_id' => $units['box']->id,
                'supplier_id' => $suppliers['globalnet']->id,
                'stock' => 12,
                'minimum_stock' => 4,
                'purchase_price' => 2100000.00,
                'selling_price' => 2450000.00,
                'is_active' => true,
            ],
            [
                'sku' => 'PRD-ACC-006',
                'name' => 'Logitech MX Master 3S Wireless Performance Mouse',
                'category_id' => $categories['aksesoris-elektronik']->id,
                'unit_id' => $units['pcs']->id,
                'supplier_id' => $suppliers['mitra']->id,
                'stock' => 2, // Low stock (<= 5)
                'minimum_stock' => 5,
                'purchase_price' => 1390000.00,
                'selling_price' => 1649000.00,
                'is_active' => true,
            ],
        ];

        foreach ($products as $productData) {
            Product::updateOrCreate(
                ['sku' => $productData['sku']],
                $productData
            );
        }
    }
}

