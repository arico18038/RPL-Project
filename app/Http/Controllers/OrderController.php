<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'table_id' => ['required', 'exists:tables,id'],
            'note' => ['nullable', 'string', 'max:500'],
            'source' => ['nullable', 'string', 'max:30'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'exists:menu_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $order = DB::transaction(function () use ($validated) {
            $requestedItems = collect($validated['items'])
                ->groupBy('id')
                ->map(fn ($items, $id) => [
                    'id' => $id,
                    'quantity' => $items->sum('quantity'),
                ])
                ->values();
            $menuIds = $requestedItems->pluck('id');
            $menus = MenuItem::whereIn('id', $menuIds)->lockForUpdate()->get()->keyBy('id');

            $items = $requestedItems->map(function ($item) use ($menus) {
                $menu = $menus->get($item['id']);
                $quantity = (int) $item['quantity'];
                $price = (float) $menu->price;

                if (!$menu->is_available || $menu->stock <= 0) {
                    throw ValidationException::withMessages([
                        'items' => "{$menu->name} sedang tidak tersedia.",
                    ]);
                }

                if ($quantity > $menu->stock) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$menu->name} hanya tersisa {$menu->stock}. Sesuaikan jumlah pesanan sebelum bayar.",
                    ]);
                }

                return [
                    'menu_item_id' => $menu->id,
                    'quantity' => $quantity,
                    'subtotal' => $price * $quantity,
                ];
            });

            $subtotal = (int) $items->sum('subtotal');
            $salesSettings = SiteSetting::salesSettings();
            $discountEnabled = ($salesSettings['discount_enabled'] ?? '0') === '1';
            $discountType = $salesSettings['discount_type'] ?? 'persen';
            $discountValue = $discountEnabled ? (float) ($salesSettings['discount_value'] ?? 0) : 0;
            $discountAmount = $discountType === 'persen'
                ? (int) round($subtotal * min($discountValue, 100) / 100)
                : (int) min($discountValue, $subtotal);
            $taxRate = (float) ($salesSettings['tax_rate'] ?? 11);
            $taxable = max($subtotal - $discountAmount, 0);
            $tax = (int) round($taxable * $taxRate / 100);
            $total = $taxable + $tax;

            $order = Order::create([
                'table_id' => $validated['table_id'],
                'status' => 'pending',
                'subtotal' => $subtotal,
                'discount_type' => $discountType,
                'discount_value' => (int) $discountValue,
                'discount_amount' => $discountAmount,
                'tax' => $tax,
                'total_price' => $total,
                'note' => $validated['note'] ?? null,
            ]);

            $order->order_items()->createMany($items->all());

            $items->each(function ($item) use ($menus) {
                $menus->get($item['menu_item_id'])->decrement('stock', $item['quantity']);
            });

            return $order;
        });

        if (($validated['source'] ?? null) === 'customer') {
            return redirect()
                ->route('customer.menu.table', $order->table?->number ?? $request->input('table_number', 1))
                ->with('success', 'Pesanan #' . $order->id . ' berhasil dikirim ke kasir.');
        }

        return redirect()
            ->route('pos.index')
            ->with('success', 'Pesanan #' . $order->id . ' berhasil dikirim ke admin.');
    }
}
