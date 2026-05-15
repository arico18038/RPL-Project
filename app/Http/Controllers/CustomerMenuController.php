<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\RestaurantTable;

class CustomerMenuController extends Controller
{
    public function index(?int $number = null)
    {
        $table = $number
            ? RestaurantTable::where('number', $number)->firstOrFail()
            : RestaurantTable::orderBy('number')->first();

        $menus = MenuItem::query()
            ->with('category')
            ->where('is_available', true)
            ->orderBy('category_id')
            ->orderBy('name')
            ->get();
        $categories = Category::orderBy('name')->get();

        return view('customer.menu', compact('menus', 'categories', 'table'));
    }
}
