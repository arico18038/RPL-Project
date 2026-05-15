# Struktur Laravel Sikasir-4SR

Proyek ini memakai Laravel 12, PHP 8.2, dan MySQL.

## Folder penting

- `routes/web.php`: route halaman kasir, admin, simpan pesanan, dan konfirmasi pembayaran.
- `app/Models/MenuItem.php`: model data menu.
- `app/Models/Order.php`: model transaksi pesanan.
- `app/Models/OrderItem.php`: model detail item pesanan.
- `app/Http/Controllers/PosController.php`: halaman kasir.
- `app/Http/Controllers/OrderController.php`: proses simpan pesanan.
- `app/Http/Controllers/AdminController.php`: dashboard admin dan konfirmasi pembayaran.
- `database/migrations`: struktur tabel MySQL.
- `database/seeders/MenuItemSeeder.php`: data awal menu.
- `resources/views/pos/index.blade.php`: tampilan kasir.
- `resources/views/admin/index.blade.php`: tampilan admin.
- `public/assets/css/app.css`: styling aplikasi.
- `public/assets/js/pos.js`: interaksi keranjang, filter, diskon, dan total.

## Setup database

1. Nyalakan Apache dan MySQL dari XAMPP.
2. Buat database bernama `sikasir_4sr` di phpMyAdmin, atau import `database/sikasir_4sr_setup.sql`.
3. Pastikan `.env` berisi konfigurasi berikut:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sikasir_4sr
DB_USERNAME=root
DB_PASSWORD=
```

4. Jalankan migrasi dan data awal:

```bash
php artisan migrate --seed
```

5. Jalankan aplikasi:

```bash
php artisan serve
```

Halaman kasir ada di `/`, dashboard admin ada di `/admin`.
