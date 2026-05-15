<?php

namespace App\Http\Controllers;

use App\Models\Order;

class AdminController extends Controller
{
    public function index()
    {
        $orders = Order::with(['order_items.menu_item', 'table'])->latest()->get();

        return view('admin.index', compact('orders'));
    }

    public function markPaid(Order $order)
    {
        $order->update(['status' => 'completed']);

        return back()->with('success', 'Pesanan berhasil dikonfirmasi.');
    }
}
