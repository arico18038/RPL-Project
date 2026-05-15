<aside class="sidebar">
    <div>
        <div class="sidebar-brand">
            <img src="{{ asset('assets/logo-4sr.png') }}" alt="Logo 4SR" class="brand-logo-img">
            <div>
                <h3>Sikasir-4SR</h3>
                <p>Sistem kasir-4SR</p>
            </div>
            <button class="sidebar-toggle" type="button" aria-label="Ciutkan sidebar">&laquo;</button>
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="{{ route('pos.index') }}" @class(['active' => ($active ?? '') === 'kasir'])>
                    Kasir
                </a>
            </li>
            <li>
                <a href="{{ route('admin.index') }}" @class(['active' => ($active ?? '') === 'barang'])>
                    Barang & Stok
                </a>
            </li>
            <li>
                <a href="{{ route('admin.history') }}" @class(['active' => ($active ?? '') === 'riwayat'])>
                    Riwayat Transaksi
                </a>
            </li>
            <li>
                <a href="{{ route('admin.orders') }}" @class(['active' => ($active ?? '') === 'pesanan'])>
                    Pesanan
                </a>
            </li>
            <li>
                <a href="{{ route('admin.report') }}" @class(['active' => ($active ?? '') === 'laporan'])>
                    Laporan
                </a>
            </li>
            <li>
                <a href="{{ route('admin.settings') }}" @class(['active' => ($active ?? '') === 'pengaturan'])>
                    Pengaturan
                </a>
            </li>
        </ul>
    </div>

    <a href="{{ route('pos.index') }}" class="logout-link">
        Keluar
    </a>
</aside>
