@extends('layouts.app')

@section('title', 'Sikasir-4SR - Menu')
@section('body_class', 'pos-body')

@section('content')
@include('partials.sidebar', ['active' => 'kasir'])

<main class="main-content" id="kasir">
    @include('partials.topbar', ['title' => 'Menu', 'subtitle' => 'Rumah Makan 4SR', 'role' => auth()->check() ? 'Admin' : 'Pengunjung'])

    @if (session('success'))
        <div class="toast-message is-visible">Produk berhasil ditambahkan</div>
    @endif

    @if ($errors->any())
        <div class="alert error">{{ $errors->first() }}</div>
    @endif

    <form class="pos-layout" method="POST" action="{{ route('orders.store') }}" id="order-form">
        @csrf

        <section class="product-area">
            <div class="search-box">
                <img src="{{ asset('images/icon/Icon Pencarian.png') }}" alt="" class="search-icon-img">
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
                    @php
                        $stock = $menu->stock;
                        $code = 'BRG-' . str_pad((string) $menu->id, 3, '0', STR_PAD_LEFT);
                        $image = $menu->image_url && str_starts_with($menu->image_url, 'http') ? $menu->image_url : null;
                    @endphp
                    <article class="product-card" data-name="{{ strtolower($menu->name) }}" data-category="{{ $menu->category_id }}">
                        <div class="product-image">
                            @if ($image)
                                <img src="{{ $image }}" alt="{{ $menu->name }}">
                            @else
                                <div class="product-placeholder">{{ strtoupper(substr($menu->name, 0, 2)) }}</div>
                            @endif
                            <span class="product-badge">{{ $menu->category?->name ?? 'Menu' }}</span>
                        </div>

                        <div class="product-info">
                            <h3>{{ $menu->name }}</h3>
                            <p class="product-code">{{ $code }}</p>

                            <div class="product-bottom">
                                <div>
                                    <p class="product-price">Rp {{ number_format($menu->price, 0, ',', '.') }}</p>
                                    <p @class(['product-stock', 'danger' => $stock < 20])>Stok: {{ $stock }}</p>
                                </div>

                                <button
                                    type="button"
                                    class="add-button"
                                    data-id="{{ $menu->id }}"
                                    data-name="{{ $menu->name }}"
                                    data-price="{{ $menu->price }}"
                                    data-stock="{{ $stock }}"
                                    @disabled($stock <= 0)
                                >
                                    @if ($stock > 0)
                                        <img src="{{ asset('images/icon/Tambah (1).png') }}" alt="Tambah" class="add-icon">
                                    @else
                                        Habis
                                    @endif
                                </button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <aside class="payment-summary">
            <div class="summary-title">
                <h2>Ringkasan Pembayaran</h2>
                <span id="cart-count" class="cart-count">0 Item</span>
            </div>

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
                    <img src="{{ asset('images/icon/Icon Kranjang Belanja.png') }}" alt="" class="empty-icon-img">
                    <p>Keranjang masih kosong</p>
                </div>
                <ul id="daftar-pesanan" class="cart-list"></ul>
                <div id="order-items-inputs"></div>
            </div>

            <div class="summary-row">
                <span>Sub total</span>
                <strong id="subtotal-harga">Rp 0</strong>
            </div>

            @auth
                <div class="discount-box">
                    <label>Diskon (F7)</label>
                    <div>
                        <select id="tipe-diskon">
                            <option value="persen">%</option>
                            <option value="rupiah">Rp</option>
                        </select>
                        <input type="number" id="nilai-diskon" value="0" min="0">
                    </div>
                </div>
            @else
                <input type="hidden" id="tipe-diskon" value="persen">
                <input type="hidden" id="nilai-diskon" value="0">
            @endauth

            <div class="summary-row">
                <span>PPN 11%</span>
                <strong id="ppn-harga">Rp 0</strong>
            </div>

            <input type="hidden" name="note" value="Pesanan dari Kasir Web">

            <div class="total-box">
                <span>Total pembayaran</span>
                <strong id="total-harga">Rp 0</strong>
            </div>

            <div class="payment-method">
                <p>Metode Pembayaran Hanya Bisa:</p>
                <button type="button">
                    <img src="{{ asset('images/icon/Uang.png') }}" alt="" class="button-icon">
                    Tunai
                </button>
            </div>

            <div class="summary-actions">
                <button class="pay-button" id="btn-bayar" type="submit" disabled>Bayar (F9)</button>
                <button class="clear-button" type="button" id="btn-clear-cart">
                    <img src="{{ asset('images/icon/Icon Hapus.png') }}" alt="" class="button-icon">
                    Hapus keranjang
                </button>
            </div>
        </aside>
    </form>
</main>
@endsection
