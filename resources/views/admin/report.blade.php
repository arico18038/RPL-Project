@extends('layouts.app')

@section('title', 'Laporan - Sikasir-4SR')
@section('body_class', 'admin-body')

@section('content')
@include('partials.sidebar', ['active' => 'laporan'])

<main class="main-content">
    @include('partials.topbar', ['title' => 'Laporan', 'subtitle' => 'Analisa keuangan dan performa toko', 'role' => 'Kasir'])

    <section class="admin-panel report-panel">
        <div class="panel-actions spread">
            <button class="outline-button" type="button">Filter periode</button>
            <button class="outline-button" type="button">Cetak Laporan</button>
        </div>

        <div class="metric-grid report-metrics">
            <div class="metric-card purple">
                <span>K</span>
                <div>
                    <p>Total transaksi</p>
                    <strong>{{ $orders->count() }}</strong>
                </div>
            </div>
            <div class="metric-card blue">
                <span>P</span>
                <div>
                    <p>Total pendapatan</p>
                    <strong>Rp {{ number_format($totalSales, 0, ',', '.') }}</strong>
                </div>
            </div>
            <div class="metric-card red">
                <span>E</span>
                <div>
                    <p>Total pengeluaran</p>
                    <strong>Rp {{ number_format($totalExpense, 0, ',', '.') }}</strong>
                </div>
            </div>
            <div class="metric-card green">
                <span>L</span>
                <div>
                    <p>Laba kotor</p>
                    <strong>Rp {{ number_format($grossProfit, 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>

        <div class="category-tabs report-tabs">
            <button type="button" class="category-btn active">Pendapatan</button>
            <button type="button" class="category-btn">Pengeluaran</button>
            <button type="button" class="category-btn">Perbandingan</button>
        </div>

        <div class="chart-card wide">
            <h3>Pendapatan Harian</h3>
            <div class="line-chart">
                <svg viewBox="0 0 900 250" preserveAspectRatio="none" aria-label="Grafik pendapatan harian">
                    <g class="grid-lines">
                        <line x1="0" y1="40" x2="900" y2="40" />
                        <line x1="0" y1="90" x2="900" y2="90" />
                        <line x1="0" y1="140" x2="900" y2="140" />
                        <line x1="0" y1="190" x2="900" y2="190" />
                    </g>
                    <polyline points="0,75 180,90 360,145 540,105 720,160 900,75" />
                    <circle cx="0" cy="75" r="4" />
                    <circle cx="180" cy="90" r="4" />
                    <circle cx="360" cy="145" r="4" />
                    <circle cx="540" cy="105" r="4" />
                    <circle cx="720" cy="160" r="4" />
                    <circle cx="900" cy="75" r="4" />
                </svg>
            </div>
        </div>

        <div class="chart-grid">
            <div class="chart-card">
                <h3>Metode Pembayaran</h3>
                <div class="donut-row">
                    <div class="donut-chart"></div>
                    <div class="legend-list">
                        <p><span class="dot green-dot"></span> Tunai <strong>1 (33,33%)</strong></p>
                        <p><span class="dot yellow-dot"></span> QRIS <strong>1 (33,33%)</strong></p>
                        <p><span class="dot blue-dot"></span> Debit <strong>1 (33,33%)</strong></p>
                    </div>
                </div>
            </div>
            <div class="chart-card">
                <h3>Pendapatan vs Pengeluaran</h3>
                <div class="bar-chart">
                    <span style="height:45%"></span><i style="height:15%"></i>
                    <span style="height:82%"></span><i style="height:35%"></i>
                    <span style="height:68%"></span><i style="height:72%"></i>
                    <span style="height:67%"></span><i style="height:73%"></i>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
