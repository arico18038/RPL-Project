<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    public static function aboutDefaults(): array
    {
        return [
            'about_kicker' => 'Sikasir-4SR',
            'about_title' => 'Sistem kasir digital untuk Rumah Makan 4SR.',
            'about_description' => 'Sikasir-4SR membantu proses pemesanan dan pembayaran menjadi lebih rapi. Melalui halaman kasir, pengguna dapat memilih produk, mengatur jumlah pesanan, melihat total pembayaran, dan mencatat transaksi.',
            'about_feature_1_title' => 'Menu Kasir',
            'about_feature_1_text' => 'Menampilkan daftar makanan dan minuman yang tersedia untuk dipilih.',
            'about_feature_2_title' => 'Pembayaran',
            'about_feature_2_text' => 'Menghitung subtotal, diskon, PPN, dan total pembayaran secara otomatis.',
            'about_feature_3_title' => 'Dashboard Admin',
            'about_feature_3_text' => 'Fitur pengelolaan barang, stok, pesanan, laporan, dan pengaturan hanya dapat diakses setelah login admin.',
        ];
    }

    public static function aboutContent(): array
    {
        $defaults = self::aboutDefaults();
        $settings = self::whereIn('key', array_keys($defaults))
            ->pluck('value', 'key')
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->all();

        return array_replace($defaults, $settings);
    }

    public static function saveMany(array $values): void
    {
        foreach ($values as $key => $value) {
            self::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
