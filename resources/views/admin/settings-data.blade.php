@extends('layouts.app')

@section('title', 'Pengaturan Data - Sikasir-4SR')
@section('body_class', 'admin-body')

@section('content')
@include('partials.sidebar', ['active' => 'pengaturan'])

<main class="main-content">
    @include('partials.topbar', ['title' => 'Data', 'subtitle' => 'Kelola data, ekspor, dan link pelanggan', 'role' => 'Kasir'])

    <section class="admin-panel settings-panel">
        <div class="settings-tabs">
            <a href="{{ route('admin.settings') }}">
                <img src="{{ asset('images/icon/Icon Setting.png') }}" alt="" class="button-icon">
                Profil Toko
            </a>
            <a href="{{ route('admin.settings.about') }}">
                <img src="{{ asset('images/icon/icon-about.png') }}" alt="" class="button-icon">
                Tentang Kami
            </a>
            <a href="{{ route('admin.settings.receipt') }}">
                <img src="{{ asset('images/icon/icon-discount-tax.png') }}" alt="" class="button-icon">
                Diskon & Pajak
            </a>
            <a class="active" href="{{ route('admin.settings.data') }}">
                <img src="{{ asset('images/icon/Laporan.png') }}" alt="" class="button-icon">
                Data
            </a>
        </div>

        <div class="settings-card">
            <h2>Manajemen Data</h2>
            <p class="muted-text">Gunakan fitur ini untuk mengambil salinan data penting dari sistem.</p>
            <div class="settings-actions-row">
                <a class="outline-button" href="{{ route('admin.inventory.export') }}">
                    <img src="{{ asset('images/icon/Laporan.png') }}" alt="" class="button-icon">
                    Ekspor Barang & Stok
                </a>
                <a class="outline-button" href="{{ route('admin.history') }}">
                    <img src="{{ asset('images/icon/Icon Riwayat.png') }}" alt="" class="button-icon">
                    Lihat Riwayat Transaksi
                </a>
            </div>
        </div>

        <div class="settings-card">
            <h2>Link Pelanggan per Meja</h2>
            <p class="muted-text">Link ini dapat dipakai untuk membuat QR code pada masing-masing meja.</p>

            <div class="table-wrapper">
                <table class="admin-table customer-link-table">
                    <thead>
                        <tr>
                            <th>Meja</th>
                            <th>Link Pelanggan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tables as $table)
                            @php
                                $customerLink = route('customer.menu.table', $table->number);
                            @endphp
                            <tr>
                                <td><strong>Meja {{ $table->number }}</strong></td>
                                <td>
                                    <input class="copy-link-input" type="text" value="{{ $customerLink }}" readonly>
                                </td>
                                <td>
                                    <button class="outline-button copy-link-button" type="button" data-copy-link="{{ $customerLink }}">
                                        <img src="{{ asset('images/icon/Aksi.png') }}" alt="" class="button-icon">
                                        Salin Link
                                    </button>
                                    <a class="outline-button" href="{{ $customerLink }}" target="_blank">
                                        <img src="{{ asset('images/icon/Mata.png') }}" alt="" class="button-icon">
                                        Buka Link
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="table-empty">Belum ada meja. Tambahkan meja dari halaman Barang & Stok.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</main>
@endsection
