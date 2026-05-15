<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PosController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PosController::class, 'index'])->name('pos.index');
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');

Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
Route::patch('/admin/orders/{order}/paid', [AdminController::class, 'markPaid'])->name('admin.orders.paid');
