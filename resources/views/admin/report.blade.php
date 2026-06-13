@extends('layouts.app')

@section('title', 'Laporan - Sikasir-4SR')
@section('body_class', 'admin-body')

@section('content')
@include('partials.sidebar', ['active' => 'laporan'])

<main class="main-content">
    @include('partials.topbar', ['title' => 'Laporan', 'subtitle' => 'Analisa keuangan dan performa toko', 'role' => 'Kasir'])

    <section class="admin-panel report-panel">
        <form class="report-filter" method="GET" action="{{ route('admin.report') }}">
            <label>
                Dari
                <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
            </label>
            <label>
                Sampai
                <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
            </label>
            <button class="primary-button" type="submit">
                <img src="{{ asset('images/icon/Kalender.png') }}" alt="" class="button-icon invert-icon">
                Filter Periode
            </button>
            <a class="outline-button" href="{{ route('admin.report.export', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}">
                <img src="{{ asset('images/icon/Laporan.png') }}" alt="" class="button-icon">
                Cetak Laporan Excel
            </a>
        </form>

        <div class="metric-grid report-metrics">
            <div class="metric-card purple">
                <span><img src="{{ asset('images/icon/Icon Riwayat.png') }}" alt="" class="metric-icon"></span>
                <div>
                    <p>Total transaksi</p>
                    <strong>{{ $orders->count() }}</strong>
                </div>
            </div>
            <div class="metric-card blue">
                <span><img src="{{ asset('images/icon/icon-revenue.png') }}" alt="" class="metric-icon"></span>
                <div>
                    <p>Total pendapatan</p>
                    <strong>Rp {{ number_format($totalSales, 0, ',', '.') }}</strong>
                </div>
            </div>
            <div class="metric-card red">
                <span><img src="{{ asset('images/icon/Icon Hapus.png') }}" alt="" class="metric-icon"></span>
                <div>
                    <p>Total pengeluaran</p>
                    <strong>Rp {{ number_format($totalExpense, 0, ',', '.') }}</strong>
                </div>
            </div>
            <div class="metric-card green">
                <span><img src="{{ asset('images/icon/icon-profit.png') }}" alt="" class="metric-icon"></span>
                <div>
                    <p>Laba kotor</p>
                    <strong>Rp {{ number_format($grossProfit, 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>

        <div class="category-tabs report-tabs">
            <button type="button" class="category-btn active" data-report-tab="pendapatan">
                <img src="{{ asset('images/icon/icon-revenue.png') }}" alt="" class="button-icon">
                Pendapatan
            </button>
            <button type="button" class="category-btn" data-report-tab="pengeluaran">
                <img src="{{ asset('images/icon/Icon Hapus.png') }}" alt="" class="button-icon">
                Pengeluaran
            </button>
            <button type="button" class="category-btn" data-report-tab="perbandingan">
                <img src="{{ asset('images/icon/Laporan.png') }}" alt="" class="button-icon">
                Perbandingan
            </button>
        </div>

        <div class="report-tab-panel active" data-report-panel="pendapatan">
            <div class="chart-card wide">
                <h3>Pendapatan Harian</h3>
                <div class="bar-chart labeled value-chart">
                    @foreach ($chartData['days'] as $day)
                        <div class="bar-group">
                            <em>Rp {{ number_format($day['sales'], 0, ',', '.') }}</em>
                            <span style="height: {{ $day['salesHeight'] }}%"></span>
                            <small>{{ $day['label'] }}</small>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="report-tab-panel" data-report-panel="pengeluaran">
            <div class="chart-card wide">
                <h3>Pengeluaran Harian</h3>
                <div class="bar-chart labeled value-chart">
                    @foreach ($chartData['days'] as $day)
                        <div class="bar-group">
                            <em>Rp {{ number_format($day['expense'], 0, ',', '.') }}</em>
                            <i style="height: {{ $day['expenseHeight'] }}%"></i>
                            <small>{{ $day['label'] }}</small>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="report-tab-panel" data-report-panel="perbandingan">
            <div class="chart-card wide">
                <h3>Pendapatan vs Pengeluaran</h3>
                <div class="bar-chart labeled compare-chart value-chart">
                    @foreach ($chartData['days'] as $day)
                        <div class="bar-group pair">
                            <em>
                                Rp {{ number_format($day['sales'], 0, ',', '.') }}<br>
                                Rp {{ number_format($day['expense'], 0, ',', '.') }}
                            </em>
                            <span style="height: {{ $day['salesHeight'] }}%"></span>
                            <i style="height: {{ $day['expenseHeight'] }}%"></i>
                            <small>{{ $day['label'] }}</small>
                        </div>
                    @endforeach
                </div>
                <div class="legend-list report-legend">
                    <p><span class="dot green-dot"></span> Pendapatan</p>
                    <p><span class="dot red-dot"></span> Pengeluaran</p>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
