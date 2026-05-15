@extends('layouts.app')

@section('title', 'Sikasir-4SR - Kasir')
@section('body_class', 'pos-body')

@section('content')
<aside class="sidebar">
    <div>
        <div class="sidebar-brand">
            <img src="{{ asset('assets/logo-4sr.png') }}" alt="Logo 4SR" class="brand-logo-img">
            <div>
                <h3>Sikasir-4SR</h3>
                <p>Sistem kasir 4SR</p>
            </div>
        </div>

        <ul class="sidebar-menu">
            <li><a href="{{ route('pos.index') }}" class="active"><span>Kasir</span></a></li>
            <li><a href="#tentang"><span>Tentang Kami</span></a></li>
        </ul>
    </div>

    <a href="{{ route('admin.index') }}" class="logout-link">Login Admin</a>
</aside>

<main class="main-content" id="kasir">
    <header class="top-header">
        <div>
            <h1>Kasir</h1>
            <p>Rumah Makan 4SR</p>
        </div>

        <div class="header-actions">
            <div class="time-card">
                <span>Waktu</span>
                <strong id="jam-sekarang">--:--:--</strong>
            </div>

            <div class="user-card">
                <div class="avatar">AU</div>
                <div>
                    <h4>Akbar Udin</h4>
                    <p>Admin Kasir</p>
                </div>
            </div>
        </div>
    </header>

    @if (session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert error">{{ $errors->first() }}</div>
    @endif

    <form class="pos-layout" method="POST" action="{{ route('orders.store') }}" id="order-form">
        @csrf

        <div class="product-area">
            <div class="search-box">
                <span>Cari</span>
                <input type="text" id="search-menu" placeholder="Cari produk (F2)">
            </div>

            <div class="category-tabs">
                <button type="button" class="category-btn active" data-category="semua">Semua produk</button>
                @foreach ($categories as $category)
                    <button type="button" class="category-btn" data-category="{{ $category->id }}">{{ $category->name }}</button>
                @endforeach
            </div>

            <div id="container-menu" class="product-grid">
                @foreach ($menus as $menu)
                    <article class="product-card" data-name="{{ strtolower($menu->name) }}" data-category="{{ $menu->category_id }}">
                        <div class="product-image">
                            <div class="product-placeholder">{{ strtoupper(substr($menu->name, 0, 2)) }}</div>
                            <span class="product-badge">{{ $menu->category?->name ?? 'Menu' }}</span>
                        </div>
                        <div class="product-info">
                            <h3>{{ $menu->name }}</h3>
                            <p class="product-code">{{ $menu->description ?? 'Menu 4SR' }}</p>
                            <div class="product-bottom">
                                <div>
                                    <p class="product-price">Rp {{ number_format($menu->price, 0, ',', '.') }}</p>
                                    <p class="product-stock">Tersedia</p>
                                </div>
                                <button type="button" class="add-button" data-id="{{ $menu->id }}" data-name="{{ $menu->name }}" data-price="{{ $menu->price }}">+</button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>

        <aside class="payment-summary">
            <h2>Ringkasan Pembayaran</h2>

            <div class="summary-section">
                <label class="table-label" for="table-id">Nomor meja</label>
                <select class="table-input" name="table_id" id="table-id" required>
                    <option value="">Pilih meja</option>
                    @foreach ($tables as $table)
                        <option value="{{ $table->id }}">Meja {{ $table->number }}</option>
                    @endforeach
                </select>
            </div>

            <div class="summary-section">
                <h4>Item Dipilih</h4>
                <div id="empty-cart-box" class="empty-cart-box">
                    <span>Keranjang masih kosong</span>
                </div>
                <ul id="daftar-pesanan" class="cart-list"></ul>
                <div id="order-items-inputs"></div>
            </div>

            <div class="summary-row">
                <span>Sub total</span>
                <strong id="subtotal-harga">Rp 0</strong>
            </div>

            <input type="hidden" id="tipe-diskon" value="rupiah">
            <input type="hidden" id="nilai-diskon" value="0">
            <input type="hidden" name="note" value="">

            <div class="total-box">
                <span>Total pembayaran</span>
                <strong id="total-harga">Rp 0</strong>
            </div>

            <div class="payment-method">
                <p>Metode Pembayaran</p>
                <button type="button">Tunai</button>
            </div>

            <button class="pay-button" id="btn-bayar" type="submit" disabled>Bayar (F9)</button>
            <button class="clear-button" type="button" id="btn-clear-cart">Hapus keranjang</button>
        </aside>
    </form>

    <section id="tentang" class="about-pos">
        <h2>Tentang Sikasir-4SR</h2>
        <p>Sikasir-4SR adalah sistem kasir digital untuk restoran yang memudahkan proses pemesanan, pembayaran, dan pengelolaan menu secara modern.</p>
    </section>
</main>
@endsection
