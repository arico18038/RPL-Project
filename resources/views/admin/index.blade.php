@extends('layouts.app')

@section('title', 'Admin Dashboard - Sikasir-4SR')
@section('body_class', 'admin-body')

@section('content')
<aside class="sidebar">
    <div>
        <div class="sidebar-brand">
            <img src="{{ asset('assets/logo-4sr.png') }}" alt="Logo 4SR" class="brand-logo-img">
            <div>
                <h3>Sikasir-4SR</h3>
                <p>Admin panel</p>
            </div>
        </div>

        <ul class="sidebar-menu">
            <li><a href="{{ route('admin.index') }}" class="active"><span>Pesanan</span></a></li>
            <li><a href="{{ route('pos.index') }}"><span>Kasir</span></a></li>
        </ul>
    </div>

    <a href="{{ route('pos.index') }}" class="logout-link">Keluar</a>
</aside>

<main class="main-content">
    <header class="top-header">
        <div>
            <h1>Dashboard Admin</h1>
            <p>Kelola pesanan dan pembayaran pelanggan</p>
        </div>

        <div class="user-card">
            <div class="avatar">AU</div>
            <div>
                <h4>Akbar Udin</h4>
                <p>Admin Kasir</p>
            </div>
        </div>
    </header>

    @if (session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif

    <section class="admin-panel">
        <div class="panel-header">
            <div>
                <h2>Konfirmasi Pesanan & Pembayaran</h2>
                <p>Data pesanan masuk dari halaman kasir.</p>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Meja</th>
                        <th>Pesanan</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>{{ $order->table ? 'Meja ' . $order->table->number : '-' }}</td>
                            <td>
                                @foreach ($order->order_items as $item)
                                    <div>{{ $item->menu_item?->name ?? 'Menu terhapus' }} x{{ $item->quantity }}</div>
                                @endforeach
                            </td>
                            <td>Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                            <td><span class="badge {{ $order->status }}">{{ $order->status === 'completed' ? 'Selesai' : ucfirst($order->status) }}</span></td>
                            <td>
                                @if ($order->status === 'pending')
                                    <form method="POST" action="{{ route('admin.orders.paid', $order) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn-konfirmasi" type="submit">Konfirmasi</button>
                                    </form>
                                @else
                                    <span class="muted-text">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="table-empty">Belum ada pesanan masuk.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</main>
@endsection
