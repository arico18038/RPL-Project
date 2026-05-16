@extends('layouts.app')

@section('title', 'Riwayat Transaksi - Sikasir-4SR')
@section('body_class', 'admin-body')

@section('content')
@include('partials.sidebar', ['active' => 'riwayat'])

<main class="main-content">
    @include('partials.topbar', ['title' => 'Riwayat Transaksi', 'subtitle' => 'Lihat dan kelola riwayat penjualan', 'role' => 'Kasir'])

    <section class="admin-panel">
        <div class="metric-grid">
            <div class="metric-card purple">
                <span>▣</span>
                <div>
                    <p>Total transaksi</p>
                    <strong>{{ $orders->count() }}</strong>
                </div>
            </div>
            <div class="metric-card blue">
                <span>▣</span>
                <div>
                    <p>Total penjualan</p>
                    <strong>Rp {{ number_format($totalSales, 0, ',', '.') }}</strong>
                </div>
            </div>
            <div class="metric-card green">
                <span>▣</span>
                <div>
                    <p>Laba kotor</p>
                    <strong>Rp {{ number_format($grossProfit, 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>

        <div class="table-filters">
            <button class="outline-button" type="button">▣ Filter periode</button>
            <div class="search-box table-search">
                <span class="search-icon">⌕</span>
                <input type="text" placeholder="Cari nama atau kode barang...">
            </div>
        </div>

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>No. Transaksi</th>
                        <th>Tanggal/Jam</th>
                        <th>Kasir</th>
                        <th>Total item</th>
                        <th>Total pembayaran</th>
                        <th>Metode bayar</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>TRX-{{ $order->created_at?->format('Y') ?? date('Y') }}-{{ str_pad((string) $order->id, 3, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $order->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>Akbar Hidayat</td>
                            <td>{{ $order->order_items->sum('quantity') }}</td>
                            <td>Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                            <td>Tunai</td>
                            <td><span class="badge {{ $order->status }}">{{ $order->status === 'completed' ? 'Selesai' : ($order->status === 'processing' ? 'Proses' : ucfirst($order->status)) }}</span></td>
                            <td class="action-cell">
                                <div class="action-buttons">
                                    <button type="button">Lihat</button>
                                    <button type="button">Cetak</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="table-empty">Belum ada riwayat transaksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <p class="table-caption">Menampilkan 1 - {{ $orders->count() }} dari {{ $orders->count() }} data</p>
    </section>
</main>
@endsection
