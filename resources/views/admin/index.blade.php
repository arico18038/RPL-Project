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

    <section class="admin-panel orders-panel">
        <div class="panel-header">
            <div>
                <h2>Konfirmasi Pesanan & Pembayaran</h2>
                <p>Data pesanan masuk dari halaman kasir.</p>
            </div>
        </div>

            <div class="order-summary-grid">
                <div class="order-summary-card">
                <span><img src="{{ asset('images/icon/pesanan.png') }}" alt="" class="inline-icon"> Total Pesanan</span>
                <strong>{{ $totalToday }}</strong>
            </div>
            <div class="order-summary-card">
                <span><img src="{{ asset('images/icon/Kalender.png') }}" alt="" class="inline-icon"> Menunggu</span>
                <strong>{{ $pendingToday }}</strong>
            </div>
            <div class="order-summary-card">
                <span><img src="{{ asset('images/icon/Aksi.png') }}" alt="" class="inline-icon"> Diproses</span>
                <strong>{{ $processingToday }}</strong>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="admin-table orders-table">
                <thead>
                    <tr>
                        <th>Meja</th>
                        <th>Detail Pesanan</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>
                                <strong>{{ $order->table ? 'Meja ' . $order->table->number : '-' }}</strong>
                                <span class="order-code">ORD-{{ str_pad((string) $order->id, 3, '0', STR_PAD_LEFT) }}</span>
                            </td>
                            <td>
                                <div class="order-items-list">
                                    @foreach ($order->order_items as $item)
                                        <span>{{ $item->menu_item?->name ?? 'Menu terhapus' }} <b>x{{ $item->quantity }}</b></span>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <strong class="order-total">Rp {{ number_format($order->total_price, 0, ',', '.') }}</strong>
                                <span class="order-time">{{ $order->created_at?->format('d/m/Y H:i') }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $order->status }}">{{ $order->status === 'completed' ? 'Selesai' : ($order->status === 'processing' ? 'Proses' : ucfirst($order->status)) }}</span>
                            </td>
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
                                            <img src="{{ asset('images/icon/icon-save.png') }}" alt="" class="button-icon invert-icon">
                                            Selesai
                                        </button>
                                    </form>
                                @else
                                    <span class="muted-text">
                                        <img src="{{ asset('images/icon/icon-save.png') }}" alt="" class="inline-icon">
                                        Selesai
                                    </span>
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

        @include('partials.pagination', ['items' => $orders])

        <p class="table-caption">
            Menampilkan {{ $orders->firstItem() ?? 0 }} - {{ $orders->lastItem() ?? 0 }} dari {{ $orders->total() }} pesanan
        </p>
    </section>
</main>
@endsection
