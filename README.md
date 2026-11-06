# Fahd IMS — Enterprise Inventory Management System

![Laravel 13](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=flat-square&logo=laravel&logoColor=white)
![PHP 8.3+](https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=flat-square&logo=php&logoColor=white)
![Tailwind CSS 4](https://img.shields.io/badge/Tailwind_CSS-4.x-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white)
![Vite 7](https://img.shields.io/badge/Vite-7.x-646CFF?style=flat-square&logo=vite&logoColor=white)
![SQLite](https://img.shields.io/badge/Database-SQLite-003B57?style=flat-square&logo=sqlite&logoColor=white)
![Tests](https://img.shields.io/badge/Tests-13%20Passed%20(65%20Assertions)-10B981?style=flat-square)

## Ringkasan Eksekutif (Executive Summary)

**Fahd IMS (Inventory Management System)** adalah sistem manajemen dan kontrol inventaris gudang berbasis web tingkat *enterprise* yang dirancang dengan fokus utama pada **integritas data stok**, **transparansi jejak audit (*audit trail*)**, dan **arsitektur kode yang bersih tanpa *bloatware***.

Setiap perubahan kuantitas barang di gudang—baik penerimaan barang (*Stock In*), pengeluaran distribusi (*Stock Out*), maupun penyesuaian stok fisik (*Stock Opname / Adjustment*)—dieksekusi secara atomik di dalam transaksi database dengan *pessimistic row-level locking* (`lockForUpdate`) serta dilengkapi *Negative Stock Guardrail* untuk menjamin saldo inventaris tidak pernah bernilai negatif atau mengalami *race condition*.

---

## Tech Stack

| Lapisan | Teknologi |
|---|---|
| **Backend Framework** | Laravel 13.x (`PHP 8.3+`) |
| **Frontend & Styling** | Blade Templates, Tailwind CSS 4 (`@tailwindcss/vite`), Vanilla ES Modules |
| **Build Tooling** | Vite 7 |
| **Iconography** | Lucide Icons (Tree-shaken ES Module integration) |
| **Database** | SQLite (Zero-configuration local persistence & `:memory:` isolated test database) |
| **Testing Framework** | PHPUnit 12 (`Feature` & `Unit` automated test suites) |

---

## Fitur Utama (Core Capabilities)

1. **Custom Role-Based Authentication (`Admin` vs `Staff`) Tanpa Package Bloatware**
   - Implementasi autentikasi kustom yang transparan (`AuthController` & middleware `EnsureUserRole`) tanpa ketergantungan pada *starter kit* berat.
   - Mendukung validasi status keaktifan akun (`is_active`), pencatatan waktu login terakhir (`last_login_at`), regenerasi sesi anti-*session fixation*, serta pemisahan hak akses operasional antara `admin` dan `staff`.

2. **Master Data Management Terintegrasi**
   - Pengelolaan katalog **Produk (`Product`)** yang terhubung secara relasional dengan **Kategori (`Category`)**, **Satuan Ukur (`Unit`)**, dan **Pemasok (`Supplier`)**.
   - Dilengkapi pencarian *real-time* berdasarkan SKU/nama produk, filter kategori, filter status ketersediaan stok (*Normal* vs *Low Stock*), serta indikator visual otomatis ketika stok mencapai atau berada di bawah ambang batas minimum (`stock <= minimum_stock`).

3. **Atomic Inventory Transactions & Pessimistic Locking**
   - Seluruh pencatatan mutasi stok (`Stock In`, `Stock Out`, dan `Stock Adjustment`) pada `StockMutationController` dibungkus dalam `DB::transaction()` dan menggunakan `Product::lockForUpdate()`.
   - Setiap transaksi menghasilkan nomor referensi unik otomatis (`IN-YYYYMMDD-XXXX`, `OUT-YYYYMMDD-XXXX`, `ADJ-YYYYMMDD-XXXX`) serta merekam *snapshot* stok sebelum (`previous_stock`) dan sesudah (`current_stock`) transaksi beserta petugas pencatatnya (`user_id`).

4. **Negative Stock Guardrail (Pencegahan Stok Minus Otomatis)**
   - Validasi berlapis di sisi *server* dan *client-side calculator* yang secara ketat menolak transaksi *Stock Out* apabila kuantitas pengeluaran melebihi saldo stok yang tersedia saat itu, membatalkan transaksi secara otomatis, dan mengembalikan pesan kesalahan yang presisi.

5. **Real-Time Operational Dashboard & Restock Alert**
   - Agregasi metrik gudang secara langsung di level database (`count()`, `sum()`, `whereColumn()`) bebas masalah *N+1 query*: Total Produk Aktif, Jumlah SKU Stok Rendah, Total Unit Masuk Hari Ini, dan Total Unit Keluar Hari Ini.
   - Menyajikan *feed* **Aktivitas Inventaris Terbaru** dan tabel **Produk Perlu Restock** dengan tombol pintasan *one-click restock*.

6. **Native UTF-8 CSV Stream Export & Print-Ready Audit Reports (`@media print`)**
   - Modul laporan (`ReportController`) mendukung filter rentang tanggal (`start_date` s/d `end_date`), tipe mutasi, dan kategori produk.
   - **Export CSV**: Menggunakan `StreamedResponse` native (`php://output`) dengan *UTF-8 BOM* sehingga langsung kompatibel saat dibuka di Microsoft Excel tanpa membebani memori server.
   - **Cetak Laporan / PDF**: Dilengkapi stylesheet `@media print` (A4 Landscape) yang secara otomatis menyembunyikan elemen navigasi/filter dan menampilkan kop surat audit resmi beserta kolom pengesahan tanda tangan petugas & auditor.

7. **Automated Feature & Security Test Suite**
   - Cakupan pengujian otomatis menyeluruh pada skenario autentikasi, otorisasi akun aktif/nonaktif, akurasi kalkulasi mutasi stok, *anti-minus stock guardrail*, filter laporan, hingga verifikasi *streamed response* CSV.

---

## Panduan Instalasi Lokal (Local Development Setup)

Pastikan environment Anda telah terpasang **PHP >= 8.3** (dengan ekstensi `pdo_sqlite`, `sqlite3`, `mbstring`, `openssl`, `fileinfo`, `gd`, `intl`, `zip`), **Composer 2**, dan **Node.js >= 20**.

```bash
# 1. Clone repository
git clone https://github.com/GHFAHD/fahd-ims-porto.git
cd fahd-ims-porto

# 2. Instal dependensi PHP & JavaScript
composer install
npm install

# 3. Salin konfigurasi environment & buat APP_KEY
cp .env.example .env
php artisan key:generate

# 4. Siapkan file database SQLite & jalankan migrasi beserta data demo (seeder)
touch database/database.sqlite
php artisan migrate:fresh --seed

# 5. Kompilasi aset frontend untuk produksi (atau gunakan `npm run dev` saat pengembangan)
npm run build

# 6. Jalankan local development server
php artisan serve
```

Akses aplikasi melalui browser di `http://127.0.0.1:8000/login`.

> **Catatan Pengguna Windows (PowerShell):**
> Untuk membuat file SQLite kosong pada langkah ke-4 jika belum tersedia, Anda dapat menjalankan:
> `New-Item -ItemType File -Path database/database.sqlite -Force`

---

## Akun Demo Bawaan (Default Demo Credentials)

Setelah menjalankan `php artisan migrate:fresh --seed`, Anda dapat langsung masuk menggunakan akun berikut (atau menggunakan tombol **Quick Fill** pada halaman login):

| Peran (Role) | Email | Password | Hak Akses Utama |
|---|---|---|---|
| **Administrator** | `admin@fahdims.test` | `password` | Akses penuh seluruh modul, Master Data CRUD, hapus produk, transaksi stok, dan laporan audit. |
| **Warehouse Staff** | `staff@fahdims.test` | `password` | Operasional gudang harian, pemantauan katalog, pencatatan Stock In/Out/Adjustment, dan ekspor laporan. |

---

## Menjalankan Pengujian Otomatis (Automated Tests)

Seluruh pengujian berjalan secara terisolasi menggunakan SQLite `:memory:` sehingga aman dijalankan kapan saja tanpa mengubah isi database lokal:

```bash
php artisan test
```

---

## Struktur Direktori Utama

```text
fahd-ims-porto/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php          # Autentikasi sesi, login, & logout
│   │   │   ├── DashboardController.php     # Agregasi metrik real-time & restock watchlist
│   │   │   ├── ProductController.php       # CRUD Master Data produk & filter katalog
│   │   │   ├── ReportController.php        # Laporan periode, cetak audit, & UTF-8 CSV stream
│   │   │   └── StockMutationController.php # Transaksi stok atomik (DB::transaction + lockForUpdate)
│   │   └── Middleware/
│   │       └── EnsureUserRole.php          # Proteksi role (admin/staff) & status keaktifan akun
│   └── Models/
│       ├── Category.php                    # Model kategori produk
│       ├── Product.php                     # Model produk & helper deteksi isLowStock()
│       ├── StockMutation.php               # Model mutasi stok & generator nomor referensi
│       ├── Supplier.php                    # Model pemasok utama
│       ├── Unit.php                        # Model satuan ukur
│       └── User.php                        # Model pengguna & helper role/inisial
├── database/
│   ├── migrations/                         # Skema tabel relasional & indeks performa
│   └── seeders/
│       ├── DatabaseSeeder.php              # Orkestrator seeder utama
│       ├── MasterDataSeeder.php            # Data awal kategori, satuan, supplier, & produk
│       └── StockMutationSeeder.php         # Riwayat transaksi awal yang sinkron dengan saldo stok
├── resources/
│   ├── css/app.css                         # Konfigurasi Tailwind CSS 4 & aturan @media print
│   ├── js/app.js                           # Inisialisasi Lucide Icons & kalkulator stok interaktif
│   └── views/
│       ├── auth/login.blade.php            # Antarmuka login enterprise + quick-fill demo
│       ├── layouts/dashboard.blade.php     # Layout utama sidebar responsif & topbar profil
│       ├── dashboard.blade.php             # Halaman ikhtisar operasional & feed mutasi
│       ├── products/                       # Katalog & form tambah/edit produk
│       ├── transactions/                   # Jurnal riwayat mutasi & form transaksi stok
│       └── reports/index.blade.php         # Halaman laporan, filter tanggal, & template cetak
└── tests/
    └── Feature/
        ├── AuthTest.php                    # Pengujian autentikasi & proteksi sesi
        ├── StockMutationTest.php           # Pengujian mutasi stok & guardrail anti-minus
        └── ReportTest.php                  # Pengujian filter laporan & ekspor CSV stream
```

---

## Lisensi

Proyek ini dilisensikan di bawah [MIT License](https://opensource.org/licenses/MIT).
