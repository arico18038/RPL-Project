<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            $menuIds = collect($validated['items'])->pluck('id');
            $menus = MenuItem::whereIn('id', $menuIds)->get()->keyBy('id');

            $items = collect($validated['items'])->map(function ($item) use ($menus) {
                $menu = $menus->get($item['id']);
                $quantity = (int) $item['quantity'];
                $price = (float) $menu->price;

                return [
                    'menu_item_id' => $menu->id,
                    'quantity' => $quantity,
                    'subtotal' => $price * $quantity,
                ];
            });

            $subtotal = $items->sum('subtotal');
            $tax = (int) round($subtotal * 0.11);
            $total = $subtotal + $tax;

            $order = Order::create([
                'table_id' => $validated['table_id'],
                'status' => 'pending',
                'total_price' => $total,
                'note' => $validated['note'] ?? null,
            ]);

            $order->order_items()->createMany($items->all());

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
