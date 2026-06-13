@php
    $customerMode = $customerMode ?? false;
    $storeProfile = \App\Models\SiteSetting::profileContent();
    $storeLogo = $storeProfile['store_logo'] ?? 'assets/logo-4sr.png';
@endphp

<aside class="sidebar">
    <div>
        <div class="sidebar-brand">
            <img src="{{ asset($storeLogo) }}" alt="Logo 4SR" class="brand-logo-img">
            <div>
                <h3>Sikasir-4SR</h3>
                <p>Sistem kasir-4SR</p>
            </div>
            <button
                class="sidebar-toggle"
                type="button"
                aria-label="Ciutkan sidebar"
                data-show-icon="{{ asset('images/icon/Icon show sidebar.png') }}"
                data-hide-icon="{{ asset('images/icon/Icon hide sidebar.png') }}"
            >
                <img src="{{ asset('images/icon/Icon hide sidebar.png') }}" alt="" class="sidebar-toggle-icon">
            </button>
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="{{ $customerMode ? '#' : route('pos.index') }}" @class(['active' => ($active ?? '') === 'kasir'])>
                    <img src="{{ asset('images/icon/Icon Kranjang Belanja.png') }}" alt="" class="nav-icon">
                    <span class="nav-label">Menu</span>
                </a>
            </li>
            @if ($customerMode || auth()->guest())
                <li>
                    <a href="{{ route('about') }}" @class(['active' => ($active ?? '') === 'tentang'])>
                        <img src="{{ asset('images/icon/icon-about.png') }}" alt="" class="nav-icon">
                        <span class="nav-label">Tentang Kami</span>
                    </a>
                </li>
            @endif

            @if (! $customerMode && auth()->check())
                <li>
                    <a href="{{ route('admin.index') }}" @class(['active' => ($active ?? '') === 'barang'])>
                        <img src="{{ asset('images/icon/Icon Barang dan Stok.png') }}" alt="" class="nav-icon">
                        <span class="nav-label">Barang & Stok</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.history') }}" @class(['active' => ($active ?? '') === 'riwayat'])>
                        <img src="{{ asset('images/icon/Icon Riwayat.png') }}" alt="" class="nav-icon">
                        <span class="nav-label">Riwayat Transaksi</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.orders') }}" @class(['active' => ($active ?? '') === 'pesanan'])>
                        <img src="{{ asset('images/icon/pesanan.png') }}" alt="" class="nav-icon">
                        <span class="nav-label">Pesanan</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.report') }}" @class(['active' => ($active ?? '') === 'laporan'])>
                        <img src="{{ asset('images/icon/Laporan.png') }}" alt="" class="nav-icon">
                        <span class="nav-label">Laporan</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.settings') }}" @class(['active' => ($active ?? '') === 'pengaturan'])>
                        <img src="{{ asset('images/icon/Icon Setting.png') }}" alt="" class="nav-icon">
                        <span class="nav-label">Pengaturan</span>
                    </a>
                </li>
            @endif
        </ul>
    </div>

    @if (! $customerMode && auth()->check())
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-link">
                <img src="{{ asset('images/icon/Icon Keluar.png') }}" alt="" class="nav-icon">
                <span class="nav-label">Keluar</span>
            </button>
        </form>
    @endif
</aside>
