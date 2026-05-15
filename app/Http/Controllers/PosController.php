<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\RestaurantTable;

class PosController extends Controller
{
    public function index()
    {
        $menus = MenuItem::query()
            ->with('category')
            ->where('is_available', true)
            ->orderBy('category_id')
            ->orderBy('name')
            ->get();
        $categories = Category::orderBy('name')->get();
        $tables = RestaurantTable::orderBy('number')->get();

        return view('pos.index', compact('menus', 'categories', 'tables'));
    }
}
