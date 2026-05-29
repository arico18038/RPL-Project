@extends('layouts.app')

@section('title', 'Pesanan - Sikasir-4SR')
@section('body_class', 'admin-body')

@section('content')
@include('partials.sidebar', ['active' => 'pesanan'])

<main class="main-content">
    @include('partials.topbar', ['title' => 'Pesanan Masuk', 'subtitle' => 'Kelola pesanan dan pembayaran pelanggan', 'role' => 'Admin Kasir'])

    @if (session('success'))
        <div class="toast-message is-visible">{{ session('success') }}</div>
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
                            <td><span class="badge {{ $order->status }}">{{ $order->status === 'completed' ? 'Selesai' : ($order->status === 'processing' ? 'Proses' : ucfirst($order->status)) }}</span></td>
                            <td>
                                @if ($order->status === 'pending')
                                    <form method="POST" action="{{ route('admin.orders.process', $order) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn-konfirmasi" type="submit">
                                            <img src="{{ asset('images/icon/Aksi.png') }}" alt="" class="button-icon invert-icon">
                                            Proses
                                        </button>
                                    </form>
                                @elseif ($order->status === 'processing')
                                    <form method="POST" action="{{ route('admin.orders.complete', $order) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn-konfirmasi" type="submit">
                                            <img src="{{ asset('images/icon/Icon Slest.png') }}" alt="" class="button-icon invert-icon">
                                            Selesai
                                        </button>
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
