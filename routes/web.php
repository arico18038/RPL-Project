<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerMenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PosController;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Route;

Route::get('/', [PosController::class, 'index'])->name('pos.index');
Route::get('/tentang-kami', fn () => view('home.about', [
    'profile' => SiteSetting::profileContent(),
    'about' => SiteSetting::aboutContent(),
]))->name('about');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/reset-password', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset.store');
});

Route::get('/menu', [CustomerMenuController::class, 'index'])->name('customer.menu');
Route::get('/menu/meja/{number}', [PosController::class, 'table'])->whereNumber('number')->name('customer.menu.table');
Route::get('/kasir', fn () => redirect()->route('pos.index'))->name('kasir.redirect');
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/admin', [AdminController::class, 'inventory'])->name('admin.index');
    Route::get('/admin/barang/export', [AdminController::class, 'exportInventory'])->name('admin.inventory.export');
    Route::post('/admin/menu', [AdminController::class, 'storeMenu'])->name('admin.menu.store');
    Route::put('/admin/menu/{menu}', [AdminController::class, 'updateMenu'])->name('admin.menu.update');
    Route::delete('/admin/menu/{menu}', [AdminController::class, 'destroyMenu'])->name('admin.menu.destroy');
    Route::post('/admin/kategori', [AdminController::class, 'storeCategory'])->name('admin.categories.store');
    Route::post('/admin/meja', [AdminController::class, 'storeTable'])->name('admin.tables.store');
    Route::delete('/admin/meja/{table}', [AdminController::class, 'destroyTable'])->name('admin.tables.destroy');
    Route::get('/admin/orders', [AdminController::class, 'orders'])->name('admin.orders');
    Route::get('/admin/riwayat', [AdminController::class, 'history'])->name('admin.history');
    Route::get('/admin/laporan', [AdminController::class, 'report'])->name('admin.report');
    Route::get('/admin/laporan/export', [AdminController::class, 'exportReport'])->name('admin.report.export');
    Route::get('/admin/pengaturan', [AdminController::class, 'settings'])->name('admin.settings');
    Route::get('/admin/pengaturan/tentang-kami', [AdminController::class, 'aboutSettings'])->name('admin.settings.about');
    Route::get('/admin/pengaturan/struk-pajak', [AdminController::class, 'receiptSettings'])->name('admin.settings.receipt');
    Route::get('/admin/pengaturan/data', [AdminController::class, 'dataSettings'])->name('admin.settings.data');
    Route::put('/admin/pengaturan', [AdminController::class, 'updateProfile'])->name('admin.settings.update');
    Route::put('/admin/pengaturan/diskon-pajak', [AdminController::class, 'updateReceipt'])->name('admin.settings.receipt.update');
    Route::put('/admin/pengaturan/tentang-kami', [AdminController::class, 'updateAbout'])->name('admin.settings.about.update');
    Route::patch('/admin/orders/{order}/process', [AdminController::class, 'markProcessing'])->name('admin.orders.process');
    Route::patch('/admin/orders/{order}/complete', [AdminController::class, 'markCompleted'])->name('admin.orders.complete');
});
