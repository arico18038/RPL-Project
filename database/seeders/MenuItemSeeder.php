<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menus = [
            ['name' => 'Nasi Goreng Spesial', 'code' => 'MKN-001', 'category' => 'makanan', 'price' => 25000, 'stock' => 30],
            ['name' => 'Ayam Geprek', 'code' => 'MKN-002', 'category' => 'makanan', 'price' => 18000, 'stock' => 25],
            ['name' => 'Mie Goreng Jawa', 'code' => 'MKN-003', 'category' => 'makanan', 'price' => 20000, 'stock' => 22],
            ['name' => 'Es Teh Manis', 'code' => 'MNM-001', 'category' => 'minuman', 'price' => 5000, 'stock' => 50],
            ['name' => 'Es Jeruk', 'code' => 'MNM-002', 'category' => 'minuman', 'price' => 7000, 'stock' => 45],
            ['name' => 'Air Mineral', 'code' => 'MNM-003', 'category' => 'minuman', 'price' => 4000, 'stock' => 60],
            ['name' => 'Kerupuk', 'code' => 'LNY-001', 'category' => 'lainnya', 'price' => 3000, 'stock' => 40],
        ];

        foreach ($menus as $menu) {
            MenuItem::updateOrCreate(['code' => $menu['code']], $menu);
        }
    }
}
