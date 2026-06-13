@extends('layouts.app')

@section('title', 'Menu 4SR - Meja ' . ($table?->number ?? '-'))
@section('body_class', 'customer-body')

@section('content')
<header class="customer-header">
    <div class="customer-brand">
        <img src="{{ asset('assets/logo-4sr.png') }}" alt="Logo 4SR">
        <div>
            <strong>CashDig-4SR</strong>
            <span>Menu Digital Rumah Makan 4SR</span>
        </div>
    </div>

    <nav>
        <a href="#menu">
            <img src="{{ asset('images/icon/Icon Barang dan Stok.png') }}" alt="" class="inline-icon invert-icon">
            Menu
        </a>
        <a href="#pesanan">
            <img src="{{ asset('images/icon/Icon Kranjang Belanja.png') }}" alt="" class="inline-icon invert-icon">
            Pesanan
        </a>
        <a href="#tentang">
            <img src="{{ asset('images/icon/icon-about.png') }}" alt="" class="inline-icon invert-icon">
            Tentang
        </a>
    </nav>
</header>

<main class="customer-page">
    <section class="customer-hero">
        <div>
            <p class="hero-kicker">Selamat datang di meja {{ $table?->number ?? '-' }}</p>
            <h1>Pilih menu favorit, pesanan langsung masuk ke kasir.</h1>
            <p>Silakan pilih makanan dan minuman dari daftar menu. Setelah dikirim, staf kami akan memproses pesanan Anda.</p>
            <a href="#menu" class="hero-button">
                <img src="{{ asset('images/icon/Mata.png') }}" alt="" class="button-icon invert-icon">
                Lihat Menu
            </a>
        </div>
    </section>

    @if (session('success'))
        <div class="customer-toast">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('orders.store') }}" id="order-form" class="customer-order-layout">
        @csrf
        <input type="hidden" name="table_id" value="{{ $table?->id }}">
        <input type="hidden" name="source" value="customer">
        <input type="hidden" name="note" value="Pesanan pelanggan dari QR meja {{ $table?->number }}">

        <section id="menu" class="customer-menu-section">
            <div class="customer-section-title">
                <span>Menu 4SR</span>
                <h2>Menu Makanan & Minuman</h2>
            </div>

            <div class="customer-filter">
                <div class="search-box">
                    <img src="{{ asset('images/icon/Icon Pencarian.png') }}" alt="" class="search-icon-img">
                    <input type="text" id="search-menu" placeholder="Cari menu...">
                </div>

                <div class="category-tabs">
                    <button type="button" class="category-btn active" data-category="semua">Semua</button>
                    @foreach ($categories as $category)
                        <button type="button" class="category-btn" data-category="{{ $category->id }}">{{ $category->name }}</button>
                    @endforeach
                </div>
            </div>

            <div class="menu-book" aria-label="Buku menu digital">
                <button class="book-nav prev" type="button" aria-label="Halaman sebelumnya">
                    <img src="{{ asset('images/icon/Icon hide sidebar.png') }}" alt="" class="button-icon invert-icon">
                </button>
                <div class="book-pages" id="book-pages">
                    @foreach ($categories as $category)
                        @php
                            $categoryMenus = $menus->where('category_id', $category->id);
                        @endphp
                        @if ($categoryMenus->isNotEmpty())
                            <section class="book-page" data-category="{{ $category->id }}">
                                <div class="book-page-cover">
                                    <span>Menu {{ $category->name }}</span>
                                    <h3>{{ $category->name }}</h3>
                                    <p>{{ $category->description ?? 'Pilihan menu dari Rumah Makan 4SR' }}</p>
                                </div>

                                <div class="book-menu-list">
                                    @foreach ($categoryMenus as $menu)
                                        @php
                                            $image = $menu->image_url
                                                ? (str_starts_with($menu->image_url, 'http') ? $menu->image_url : asset($menu->image_url))
                                                : null;
                                        @endphp
                                        <article class="book-menu-item product-card" data-name="{{ strtolower($menu->name) }}" data-category="{{ $menu->category_id }}">
                                            <div class="book-menu-image">
                                                @if ($image)
                                                    <img src="{{ $image }}" alt="{{ $menu->name }}">
                                                @else
                                                    <div class="customer-product-placeholder">{{ strtoupper(substr($menu->name, 0, 2)) }}</div>
                                                @endif
                                            </div>
                                            <div class="book-menu-info">
                                                <span>{{ $menu->category?->name ?? 'Menu' }}</span>
                                                <h3>{{ $menu->name }}</h3>
                                                <p>{{ $menu->description ?? 'Menu pilihan Rumah Makan 4SR' }}</p>
                                                <div>
                                                    <strong>Rp {{ number_format($menu->price, 0, ',', '.') }}</strong>
                                                <button type="button" class="add-button" data-id="{{ $menu->id }}" data-name="{{ $menu->name }}" data-price="{{ $menu->price }}" data-stock="{{ $menu->stock }}">
                                                    <img src="{{ asset('images/icon/Tambah (1).png') }}" alt="" class="add-icon">
                                                    Tambah
                                                </button>
                                                </div>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            </section>
                        @endif
                    @endforeach
                </div>
                <button class="book-nav next" type="button" aria-label="Halaman berikutnya">
                    <img src="{{ asset('images/icon/Icon show sidebar.png') }}" alt="" class="button-icon invert-icon">
                </button>
            </div>

            <div class="book-hint">Gunakan tombol panah untuk membalik lembar menu.</div>
        </section>

        <aside id="pesanan" class="customer-cart payment-summary">
            <div class="summary-title">
                <h2>Pesanan Meja {{ $table?->number ?? '-' }}</h2>
                <span id="cart-count" class="cart-count">0 Item</span>
            </div>

            <div class="summary-section">
                <h4>Item Dipilih</h4>
                <div id="empty-cart-box" class="empty-cart-box">
                    <img src="{{ asset('images/icon/Icon Kranjang Belanja.png') }}" alt="" class="empty-icon-img">
                    <p>Belum ada menu dipilih</p>
                </div>
                <ul id="daftar-pesanan" class="cart-list"></ul>
                <div id="order-items-inputs"></div>
            </div>

            <div class="summary-row">
                <span>Sub total</span>
                <strong id="subtotal-harga">Rp 0</strong>
            </div>

            @php
                $discountEnabled = ($salesSettings['discount_enabled'] ?? '0') === '1' && (float) ($salesSettings['discount_value'] ?? 0) > 0;
                $discountType = $salesSettings['discount_type'] ?? 'persen';
                $discountValue = (float) ($salesSettings['discount_value'] ?? 0);
                $taxRate = (float) ($salesSettings['tax_rate'] ?? 11);
            @endphp

            @if ($discountEnabled)
                <div class="summary-row discount-summary-row">
                    <span>Diskon {{ $discountType === 'persen' ? rtrim(rtrim(number_format($discountValue, 2, ',', '.'), '0'), ',') . '%' : 'Rp ' . number_format($discountValue, 0, ',', '.') }}</span>
                    <strong id="diskon-harga">- Rp 0</strong>
                </div>
                <input type="hidden" id="tipe-diskon" value="{{ $discountType }}">
                <input type="hidden" id="nilai-diskon" value="{{ $discountValue }}">
            @else
                <input type="hidden" id="tipe-diskon" value="rupiah">
                <input type="hidden" id="nilai-diskon" value="0">
            @endif

            <input type="hidden" id="tax-rate" value="{{ $taxRate }}">
            <div class="summary-row">
                <span>PPN {{ rtrim(rtrim(number_format($taxRate, 2, ',', '.'), '0'), ',') }}%</span>
                <strong id="ppn-harga">Rp 0</strong>
            </div>

            <div class="total-box">
                <span>Total pembayaran</span>
                <strong id="total-harga">Rp 0</strong>
            </div>

            <div class="summary-actions">
                <button class="pay-button" id="btn-bayar" type="submit" disabled>
                    <img src="{{ asset('images/icon/pesanan.png') }}" alt="" class="button-icon invert-icon">
                    Kirim Pesanan
                </button>
                <button class="clear-button" type="button" id="btn-clear-cart">
                    <img src="{{ asset('images/icon/Icon Hapus.png') }}" alt="" class="button-icon">
                    Hapus pesanan
                </button>
            </div>
        </aside>
    </form>

    <section id="tentang" class="customer-about">
        <h2>Tentang CashDig-4SR</h2>
        <p>CashDig-4SR membantu pelanggan memesan dari meja melalui QR code, lalu pesanan masuk ke dashboard kasir dan admin.</p>
    </section>
</main>
@endsection
