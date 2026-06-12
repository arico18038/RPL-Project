<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\RestaurantTable;
use App\Models\SiteSetting;

class PosController extends Controller
{
    public function index()
    {
        return $this->showMenu();
    }

    public function table(int $number)
    {
        $selectedTable = RestaurantTable::where('number', $number)->firstOrFail();

        return $this->showMenu($selectedTable);
    }

    private function showMenu(?RestaurantTable $selectedTable = null)
    {
        $menus = MenuItem::query()
            ->with('category')
            ->where('is_available', true)
            ->orderBy('category_id')
            ->orderBy('name')
            ->get();
        $categories = Category::orderBy('name')->get();
        $tables = RestaurantTable::orderBy('number')->get();
        $salesSettings = SiteSetting::salesSettings();

        return view('pos.index', compact('menus', 'categories', 'tables', 'selectedTable', 'salesSettings'));
    }
}
