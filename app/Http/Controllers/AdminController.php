<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function inventory()
    {
        $menus = MenuItem::with('category')
            ->orderBy('category_id')
            ->orderBy('name')
            ->get();
        $categories = Category::orderBy('name')->get();
        $lowStockCount = $menus->filter(fn ($menu, $index) => $this->displayStock($menu, $index) < 20)->count();

        return view('admin.inventory', compact('menus', 'categories', 'lowStockCount'));
    }

    public function orders()
    {
        $orders = Order::with(['order_items.menu_item', 'table'])->latest()->get();

        return view('admin.index', compact('orders'));
    }

    public function history()
    {
        $orders = Order::with(['order_items.menu_item', 'table'])
            ->whereIn('status', ['completed', 'processing', 'pending'])
            ->latest()
            ->get();
        $totalSales = $orders->sum('total_price');
        $grossProfit = max($totalSales * 0.45, 0);

        return view('admin.history', compact('orders', 'totalSales', 'grossProfit'));
    }

    public function report()
    {
        $orders = Order::with('order_items')
            ->whereIn('status', ['completed', 'processing', 'pending'])
            ->latest()
            ->get();
        $totalSales = $orders->sum('total_price');
        $totalExpense = $totalSales * 0.61;
        $grossProfit = max($totalSales - $totalExpense, 0);

        return view('admin.report', compact('orders', 'totalSales', 'totalExpense', 'grossProfit'));
    }

    public function settings()
    {
        return view('admin.settings');
    }

    public function storeMenu(Request $request)
    {
        $validated = $this->validateMenu($request);

        MenuItem::create([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'price' => $validated['price'],
            'description' => $validated['description'] ?? null,
            'image_url' => $this->normalizeImageUrl($validated['image_url'] ?? null),
            'is_available' => (bool) $validated['is_available'],
        ]);

        return redirect()
            ->route('admin.index')
            ->with('success', 'Menu baru berhasil ditambahkan.');
    }

    public function updateMenu(Request $request, MenuItem $menu)
    {
        $validated = $this->validateMenu($request);

        $menu->update([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'price' => $validated['price'],
            'description' => $validated['description'] ?? null,
            'image_url' => $this->normalizeImageUrl($validated['image_url'] ?? null),
            'is_available' => (bool) $validated['is_available'],
        ]);

        return redirect()
            ->route('admin.index')
            ->with('success', 'Menu berhasil diperbarui.');
    }

    public function destroyMenu(MenuItem $menu)
    {
        OrderItem::where('menu_item_id', $menu->id)->update(['menu_item_id' => null]);
        $menu->delete();

        return redirect()
            ->route('admin.index')
            ->with('success', 'Menu berhasil dihapus.');
    }

    public function markProcessing(Order $order)
    {
        $order->update(['status' => 'processing']);

        return back()->with('success', 'Pesanan sedang diproses.');
    }

    public function markCompleted(Order $order)
    {
        $order->update(['status' => 'completed']);

        return back()->with('success', 'Pesanan selesai.');
    }

    private function validateMenu(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'is_available' => ['required', Rule::in(['0', '1'])],
        ], [
            'name.required' => 'Nama barang wajib diisi.',
            'name.max' => 'Nama barang maksimal 255 karakter.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak tersedia.',
            'price.required' => 'Harga jual wajib diisi.',
            'price.numeric' => 'Harga jual harus berupa angka.',
            'price.min' => 'Harga jual tidak boleh kurang dari 0.',
            'description.max' => 'Deskripsi maksimal 1000 karakter.',
            'image_url.url' => 'URL gambar harus berupa tautan yang valid.',
            'image_url.max' => 'URL gambar terlalu panjang. Maksimal 2048 karakter.',
            'is_available.required' => 'Status aktif wajib dipilih.',
        ]);
    }

    private function displayStock(MenuItem $menu, int $index): int
    {
        return match ($menu->id) {
            5 => 3,
            default => max(12, 133 - (($index + 1) * 13)),
        };
    }

    private function normalizeImageUrl(?string $imageUrl): ?string
    {
        if (!$imageUrl) {
            return null;
        }

        $parts = parse_url($imageUrl);
        if (($parts['host'] ?? '') && str_contains($parts['host'], 'google.') && isset($parts['query'])) {
            parse_str($parts['query'], $query);

            if (!empty($query['imgurl'])) {
                return $query['imgurl'];
            }
        }

        return $imageUrl;
    }
}
