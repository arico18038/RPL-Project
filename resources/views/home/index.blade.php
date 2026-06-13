@extends('layouts.app')

@section('title', 'Cash-Dig 4SR')
@section('body_class', 'public-body')

@section('content')
<main class="public-home">
    <nav class="public-nav">
        <div class="public-brand">
            <img src="{{ asset('assets/logo-4sr.png') }}" alt="Logo 4SR">
            <div>
                <strong>Cash-Dig 4SR</strong>
                <span>Sistem kasir rumah makan</span>
            </div>
        </div>
        <a href="{{ route('login') }}" class="public-login-link">
            <img src="{{ asset('images/icon/Icon Keluar.png') }}" alt="" class="button-icon">
            Login Admin
        </a>
    </nav>

    <section class="public-hero">
        <div class="public-hero-copy">
            <p class="public-kicker">Rumah Makan 4SR</p>
            <h1>Selamat datang di Cash-Dig</h1>
            <p>
                Sistem kasir digital untuk membantu pengelolaan menu, stok, pesanan,
                riwayat transaksi, dan laporan penjualan secara lebih rapi.
            </p>
            <div class="public-actions">
                <a href="{{ route('pos.index') }}" class="primary-button">
                    <img src="{{ asset('images/icon/Icon Kranjang Belanja.png') }}" alt="" class="button-icon invert-icon">
                    Menu Kasir
                </a>
                <a href="{{ route('about') }}" class="outline-button">
                    <img src="{{ asset('images/icon/icon-about.png') }}" alt="" class="button-icon">
                    Tentang Kami
                </a>
            </div>
        </div>

        <div class="public-hero-panel">
            <span>
                <img src="{{ asset('images/icon/Icon Barang dan Stok.png') }}" alt="" class="inline-icon invert-icon">
                4SR
            </span>
            <h2>Kasir, stok, dan pesanan dalam satu dashboard.</h2>
            <p>Menu admin hanya dapat diakses setelah login.</p>
        </div>
    </section>
</main>
@endsection
