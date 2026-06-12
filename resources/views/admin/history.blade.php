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
                <span><img src="{{ asset('images/icon/Icon Riwayat.png') }}" alt="" class="metric-icon"></span>
                <div>
                    <p>Total transaksi {{ $periodLabel }}</p>
                    <strong>{{ $totalTransactions }}</strong>
                </div>
            </div>
            <div class="metric-card blue">
                <span><img src="{{ asset('images/icon/Uang.png') }}" alt="" class="metric-icon"></span>
                <div>
                    <p>Total penjualan {{ $periodLabel }}</p>
                    <strong>Rp {{ number_format($totalSales, 0, ',', '.') }}</strong>
                </div>
            </div>
            <div class="metric-card green">
                <span><img src="{{ asset('images/icon/Laba.png') }}" alt="" class="metric-icon"></span>
                <div>
                    <p>Laba kotor {{ $periodLabel }}</p>
                    <strong>Rp {{ number_format($grossProfit, 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>

        <div class="table-filters">
            <form class="period-filter" method="GET" action="{{ route('admin.history') }}">
                <label for="recap-type">Rekap</label>
                <select id="recap-type" name="recap_type">
                    <option value="daily" @selected($recapType === 'daily')>Harian</option>
                    <option value="monthly" @selected($recapType === 'monthly')>Bulanan</option>
                    <option value="yearly" @selected($recapType === 'yearly')>Tahunan</option>
                </select>
                <input
                    data-period-input="daily"
                    type="date"
                    name="date"
                    value="{{ $selectedDate }}"
                    aria-label="Pilih tanggal rekap"
                >
                <input
                    data-period-input="monthly"
                    type="month"
                    name="month"
                    value="{{ $selectedMonth }}"
                    aria-label="Pilih bulan rekap"
                >
                <input
                    data-period-input="yearly"
                    type="number"
                    name="year"
                    min="2020"
                    max="2100"
                    value="{{ $selectedYear }}"
                    aria-label="Pilih tahun rekap"
                >
                <button class="primary-button" type="submit">Terapkan</button>
                <a class="outline-link" href="{{ route('admin.history') }}">Reset</a>
            </form>
            <div class="search-box table-search">
                <img src="{{ asset('images/icon/Icon Pencarian.png') }}" alt="" class="search-icon-img">
                <input type="text" placeholder="Cari transaksi atau kasir...">
            </div>
        </div>

        <p class="filter-summary">Menampilkan rekapitulasi {{ strtolower($periodLabel) }}.</p>

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
                        @php
                            $transactionCode = 'TRX-' . ($order->created_at?->format('Y') ?? date('Y')) . '-' . str_pad((string) $order->id, 3, '0', STR_PAD_LEFT);
                            $items = $order->order_items->map(function ($item) {
                                $quantity = (int) $item->quantity;
                                $subtotal = (float) $item->subtotal;
                                $price = $quantity > 0 ? $subtotal / $quantity : $subtotal;

                                return [
                                    'name' => $item->menu_item?->name ?? 'Menu terhapus',
                                    'quantity' => $quantity,
                                    'price' => $price,
                                    'subtotal' => $subtotal,
                                ];
                            })->values();
                            $calculatedSubtotal = (float) $items->sum('subtotal');
                            $subtotal = (float) ($order->subtotal ?: $calculatedSubtotal ?: $order->total_price);
                            $discountAmount = (float) ($order->discount_amount ?? 0);
                            $tax = (float) ($order->tax ?? 0);
                            $total = (float) $order->total_price;
                            $statusLabel = $order->status === 'completed' ? 'Selesai' : ($order->status === 'processing' ? 'Proses' : ucfirst($order->status));
                            $receiptPayload = [
                                'store' => [
                                    'name' => $profile['store_name'] ?? 'Rumah Makan 4SR',
                                    'address' => $profile['store_address'] ?? '',
                                    'phone' => $profile['store_phone'] ?? '',
                                    'whatsapp' => $profile['store_whatsapp'] ?? '',
                                ],
                                'transactionCode' => $transactionCode,
                                'date' => $order->created_at?->format('d/m/Y H:i') ?? '-',
                                'cashier' => auth()->user()?->name ?? 'Admin Cash-Dig',
                                'table' => $order->table ? 'Meja ' . $order->table->number : '-',
                                'items' => $items,
                                'subtotal' => $subtotal,
                                'discountType' => $order->discount_type ?? 'persen',
                                'discountValue' => (float) ($order->discount_value ?? 0),
                                'discountAmount' => $discountAmount,
                                'tax' => $tax,
                                'total' => $total,
                                'paymentMethod' => 'Tunai',
                                'status' => $statusLabel,
                            ];
                        @endphp
                        <tr>
                            <td>{{ $transactionCode }}</td>
                            <td>{{ $order->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>{{ auth()->user()?->name ?? 'Admin Cash-Dig' }}</td>
                            <td>{{ $order->order_items->sum('quantity') }}</td>
                            <td>Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                            <td>Tunai</td>
                            <td><span class="badge {{ $order->status }}">{{ $statusLabel }}</span></td>
                            <td class="action-cell">
                                <div class="action-buttons">
                                    <button type="button" class="receipt-view-button" data-receipt-id="{{ $order->id }}">
                                        <img src="{{ asset('images/icon/Mata.png') }}" alt="" class="button-icon">
                                        Lihat
                                    </button>
                                    <button type="button" class="receipt-print-button" data-receipt-id="{{ $order->id }}">
                                        <img src="{{ asset('images/icon/Aksi.png') }}" alt="" class="button-icon">
                                        Cetak
                                    </button>
                                </div>
                                <script type="application/json" id="receipt-data-{{ $order->id }}">
                                    {!! json_encode($receiptPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
                                </script>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="table-empty">Belum ada riwayat transaksi pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('partials.pagination', ['items' => $orders])

        <p class="table-caption">
            Menampilkan {{ $orders->firstItem() ?? 0 }} - {{ $orders->lastItem() ?? 0 }}
            dari {{ $orders->total() }} data periode {{ strtolower($periodLabel) }}
        </p>
    </section>

    <div class="modal-backdrop" id="receipt-modal" hidden>
        <div class="modal-card receipt-modal-card">
            <div class="modal-header">
                <h2>Detail Struk</h2>
                <button type="button" id="close-receipt-modal" aria-label="Tutup">x</button>
            </div>

            <div class="receipt-preview" id="receipt-preview"></div>

            <div class="modal-actions">
                <button class="outline-button" type="button" id="cancel-receipt-modal">Tutup</button>
                <button class="primary-button" type="button" id="print-receipt-modal">
                    <img src="{{ asset('images/icon/Aksi.png') }}" alt="" class="button-icon invert-icon">
                    Cetak Struk
                </button>
            </div>
        </div>
    </div>
</main>
@endsection
