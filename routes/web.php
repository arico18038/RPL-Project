<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CustomerMenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PosController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PosController::class, 'index'])->name('pos.index');
Route::get('/menu', [CustomerMenuController::class, 'index'])->name('customer.menu');
Route::get('/menu/meja/{number}', [CustomerMenuController::class, 'index'])->name('customer.menu.table');
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');

Route::get('/admin', [AdminController::class, 'inventory'])->name('admin.index');
Route::post('/admin/menu', [AdminController::class, 'storeMenu'])->name('admin.menu.store');
Route::put('/admin/menu/{menu}', [AdminController::class, 'updateMenu'])->name('admin.menu.update');
Route::delete('/admin/menu/{menu}', [AdminController::class, 'destroyMenu'])->name('admin.menu.destroy');
Route::get('/admin/orders', [AdminController::class, 'orders'])->name('admin.orders');
Route::get('/admin/riwayat', [AdminController::class, 'history'])->name('admin.history');
Route::get('/admin/laporan', [AdminController::class, 'report'])->name('admin.report');
Route::get('/admin/pengaturan', [AdminController::class, 'settings'])->name('admin.settings');
Route::patch('/admin/orders/{order}/process', [AdminController::class, 'markProcessing'])->name('admin.orders.process');
Route::patch('/admin/orders/{order}/complete', [AdminController::class, 'markCompleted'])->name('admin.orders.complete');
