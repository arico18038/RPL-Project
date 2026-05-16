@extends('layouts.app')

@section('title', 'Tentang Kami - Sikasir-4SR')
@section('body_class', 'pos-body')

@section('content')
@include('partials.sidebar', ['active' => 'tentang'])

<main class="main-content">
    @include('partials.topbar', ['title' => 'Tentang Kami', 'subtitle' => 'Rumah Makan 4SR', 'role' => auth()->check() ? 'Admin' : 'Pengunjung'])

    <section class="about-panel">
        <p class="public-kicker">Sikasir-4SR</p>
        <h1>Sistem kasir digital untuk Rumah Makan 4SR.</h1>
        <p>
            Sikasir-4SR membantu proses pemesanan dan pembayaran menjadi lebih rapi.
            Melalui halaman kasir, pengguna dapat memilih produk, mengatur jumlah
            pesanan, melihat total pembayaran, dan mencatat transaksi.
        </p>

        <div class="about-grid">
            <article>
                <h2>Menu Kasir</h2>
                <p>Menampilkan daftar makanan dan minuman yang tersedia untuk dipilih.</p>
            </article>
            <article>
                <h2>Pembayaran</h2>
                <p>Menghitung subtotal, diskon, PPN, dan total pembayaran secara otomatis.</p>
            </article>
            <article>
                <h2>Dashboard Admin</h2>
                <p>Fitur pengelolaan barang, stok, pesanan, laporan, dan pengaturan hanya dapat diakses setelah login admin.</p>
            </article>
        </div>
    </section>
</main>
@endsection
