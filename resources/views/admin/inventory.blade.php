@extends('layouts.app')

@section('title', 'Daftar Barang - Sikasir-4SR')
@section('body_class', 'admin-body')

@section('content')
@include('partials.sidebar', ['active' => 'barang'])

<main class="main-content">
    @include('partials.topbar', ['title' => 'Daftar Barang', 'subtitle' => 'Rumah Makan 4SR', 'role' => 'Admin Kasir'])

    <section class="admin-panel">
        @if (session('success'))
            <div class="toast-message is-visible">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert error">{{ $errors->first() }}</div>
        @endif

        <div class="panel-actions">
            <a class="outline-button" href="{{ route('admin.inventory.export') }}">
                <img src="{{ asset('images/icon/Laporan.png') }}" alt="" class="button-icon">
                Ekspor Excel
            </a>
            <button class="outline-button" type="button" id="open-category-modal">
                <img src="{{ asset('images/icon/Tambah (1).png') }}" alt="" class="button-icon">
                Tambah Kategori
            </button>
            <button class="primary-button" type="button" id="open-menu-modal">
                <img src="{{ asset('images/icon/Tambah (1).png') }}" alt="" class="button-icon invert-icon">
                Tambah Barang
            </button>
            <button class="primary-button" type="button" id="open-table-modal">
                <img src="{{ asset('images/icon/Tambah (1).png') }}" alt="" class="button-icon invert-icon">
                Tambah Meja
            </button>
            <button class="outline-button danger-outline-button" type="button" id="open-delete-table-modal">
                <img src="{{ asset('images/icon/Icon Hapus.png') }}" alt="" class="button-icon">
                Hapus Meja
            </button>
        </div>

        @if ($lowStockCount > 0)
            <div class="stock-alert">{{ $lowStockCount }} barang memiliki stok di bawah minimum</div>
        @endif

        <div class="table-filters">
            <form method="GET" action="{{ route('admin.index') }}" class="category-filter-form">
            <select name="category_id" onchange="this.form.submit()">
                <option value="all" @selected($selectedCategory === 'all')>Semua kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) $selectedCategory === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            </form>

            <div class="search-box table-search">
                <img src="{{ asset('images/icon/Icon Pencarian.png') }}" alt="" class="search-icon-img">
                <input type="text" placeholder="Cari nama atau kode barang...">
            </div>
        </div>

        <div class="table-wrapper">
            <table class="admin-table inventory-table">
                <thead>
                    <tr>
                        <th>Kode barang</th>
                        <th>Nama barang</th>
                        <th>Kategori</th>
                        <th>Satuan</th>
                        <th>Harga beli</th>
                        <th>Harga jual</th>
                        <th>Stok</th>
                        <th>Min. stok</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($menus as $menu)
                        @php
                            $stock = $menu->stock;
                            $sellPrice = (float) $menu->price;
                            $buyPrice = $sellPrice * 0.55;
                        @endphp
                        <tr>
                            <td>BRG-{{ str_pad((string) $menu->id, 3, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $menu->name }}</td>
                            <td>{{ $menu->category?->name ?? '-' }}</td>
                            <td>{{ $menu->unit ?? (str_contains(strtolower($menu->category?->name ?? ''), 'minuman') ? 'Botol' : 'Pcs') }}</td>
                            <td>Rp {{ number_format($buyPrice, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($sellPrice, 0, ',', '.') }}</td>
                            <td @class(['danger-text' => $stock < 20])>{{ $stock }}</td>
                            <td>20</td>
                            <td><span class="badge {{ $menu->is_available ? 'completed' : 'pending' }}">{{ $menu->is_available ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td class="action-cell">
                                <div class="action-buttons">
                                    <button
                                        type="button"
                                        class="edit-menu-button"
                                        data-update-url="{{ route('admin.menu.update', $menu) }}"
                                        data-delete-url="{{ route('admin.menu.destroy', $menu) }}"
                                        data-name="{{ $menu->name }}"
                                        data-category-id="{{ $menu->category_id }}"
                                        data-price="{{ (float) $menu->price }}"
                                        data-stock="{{ $stock }}"
                                        data-unit="{{ $menu->unit ?? 'Pcs' }}"
                                        data-description="{{ $menu->description }}"
                                        data-image-url="{{ $menu->image_url }}"
                                        data-is-available="{{ $menu->is_available ? '1' : '0' }}"
                                    >
                                        <img src="{{ asset('images/icon/Pencil.png') }}" alt="" class="button-icon">
                                        Edit
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="table-empty">Belum ada data menu.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('partials.pagination', ['items' => $menus])

        <p class="table-caption">
            Menampilkan {{ $menus->firstItem() ?? 0 }} - {{ $menus->lastItem() ?? 0 }} dari {{ $menus->total() }} data
        </p>
    </section>

    <div class="modal-backdrop" id="menu-modal" hidden>
        <div class="modal-card">
            <div class="modal-header">
                <h2 id="menu-modal-title">Tambah Barang Baru</h2>
                <button type="button" id="close-menu-modal" aria-label="Tutup">x</button>
            </div>

            <form method="POST" action="{{ route('admin.menu.store') }}" enctype="multipart/form-data" class="menu-form" id="menu-form" data-store-url="{{ route('admin.menu.store') }}">
                @csrf
                <input type="hidden" name="_method" value="POST" id="menu-form-method">
                <p id="menu-modal-description">Masukkan detail barang baru.</p>

                <label for="name">Nama barang *</label>
                <input id="name" name="name" type="text" placeholder="Contoh: Mie Goreng Telur" value="{{ old('name') }}" required>

                <div class="form-grid">
                    <div>
                        <label for="category_id">Kategori *</label>
                        <select id="category_id" name="category_id" required>
                            <option value="">Pilih kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="price">Harga jual (Rp) *</label>
                        <input id="price" name="price" type="number" min="0" step="500" placeholder="Contoh: 15000" value="{{ old('price') }}" required>
                    </div>
                    <div>
                        <label for="stock">Stok *</label>
                        <input id="stock" name="stock" type="number" min="0" step="1" placeholder="Contoh: 50" value="{{ old('stock', 100) }}" required>
                    </div>
                    <div>
                        <label for="unit">Satuan *</label>
                        <select id="unit" name="unit" required>
                            @foreach (['Pcs', 'Porsi', 'Botol', 'Gelas', 'Cup', 'Pack'] as $unit)
                                <option value="{{ $unit }}" @selected(old('unit', 'Pcs') === $unit)>{{ $unit }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <label for="description">Deskripsi (opsional)</label>
                <textarea id="description" name="description" placeholder="Deskripsi singkat barang">{{ old('description') }}</textarea>

                <label for="image_url">URL gambar produk (opsional)</label>
                <input id="image_url" name="image_url" type="url" placeholder="https://..." value="{{ old('image_url') }}">

                <label for="image_file">Upload gambar dari lokal (opsional)</label>
                <input id="image_file" name="image_file" type="file" accept="image/*">

                <label for="is_available">Status aktif *</label>
                <select id="is_available" name="is_available" required>
                    <option value="1" @selected(old('is_available', '1') === '1')>Aktif</option>
                    <option value="0" @selected(old('is_available') === '0')>Nonaktif</option>
                </select>

                <div class="modal-actions">
                    <button class="danger-button" type="submit" form="delete-menu-form" id="delete-menu-button" hidden>
                        <img src="{{ asset('images/icon/Icon Hapus.png') }}" alt="" class="button-icon invert-icon">
                        Hapus Barang
                    </button>
                    <button class="outline-button" type="button" id="cancel-menu-modal">
                        <img src="{{ asset('images/icon/Icon Hapus.png') }}" alt="" class="button-icon">
                        Batal
                    </button>
                    <button class="primary-button" type="submit">
                        <img src="{{ asset('images/icon/icon-save.png') }}" alt="" class="button-icon invert-icon">
                        Simpan
                    </button>
                </div>
            </form>

            <form method="POST" action="#" id="delete-menu-form" hidden>
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>

    <div class="modal-backdrop" id="category-modal" hidden>
        <div class="modal-card compact-modal">
            <div class="modal-header">
                <h2>Tambah Kategori</h2>
                <button type="button" id="close-category-modal" aria-label="Tutup">x</button>
            </div>

            <form method="POST" action="{{ route('admin.categories.store') }}" class="menu-form">
                @csrf
                <p>Tambahkan kategori baru untuk pengelompokan menu.</p>

                <label for="category-name">Nama kategori *</label>
                <input id="category-name" name="name" type="text" placeholder="Contoh: Makanan Berat" required>

                <label for="category-description">Deskripsi (opsional)</label>
                <textarea id="category-description" name="description" placeholder="Deskripsi singkat kategori"></textarea>

                <div class="modal-actions">
                    <button class="outline-button" type="button" id="cancel-category-modal">
                        <img src="{{ asset('images/icon/Icon Hapus.png') }}" alt="" class="button-icon">
                        Batal
                    </button>
                    <button class="primary-button" type="submit">
                        <img src="{{ asset('images/icon/icon-save.png') }}" alt="" class="button-icon invert-icon">
                        Simpan Kategori
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-backdrop" id="table-modal" hidden>
        <div class="modal-card compact-modal">
            <div class="modal-header">
                <h2>Tambah Meja Baru</h2>
                <button type="button" id="close-table-modal" aria-label="Tutup">x</button>
            </div>

            <form method="POST" action="{{ route('admin.tables.store') }}" class="menu-form">
                @csrf
                <p>Masukkan nomor meja yang akan dipakai pada pilihan meja kasir.</p>

                <label for="table-number">Nomor meja *</label>
                <input id="table-number" name="number" type="number" min="1" max="999" placeholder="Contoh: 12" value="{{ old('number') }}" required>

                <div class="modal-actions">
                    <button class="outline-button" type="button" id="cancel-table-modal">
                        <img src="{{ asset('images/icon/Icon Hapus.png') }}" alt="" class="button-icon">
                        Batal
                    </button>
                    <button class="primary-button" type="submit">
                        <img src="{{ asset('images/icon/icon-save.png') }}" alt="" class="button-icon invert-icon">
                        Simpan Meja
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-backdrop" id="delete-table-modal" hidden>
        <div class="modal-card compact-modal">
            <div class="modal-header">
                <h2>Hapus Meja</h2>
                <button type="button" id="close-delete-table-modal" aria-label="Tutup">x</button>
            </div>

            <form method="POST" action="#" class="menu-form" id="delete-table-form">
                @csrf
                @method('DELETE')
                <p>Pilih meja yang ingin dihapus. Meja yang sudah digunakan pada pesanan tidak dapat dihapus.</p>

                <label for="delete-table-select">Nomor meja *</label>
                <select id="delete-table-select" required>
                    <option value="">Pilih meja</option>
                    @foreach ($tables as $table)
                        <option value="{{ route('admin.tables.destroy', $table) }}">Meja {{ $table->number }}</option>
                    @endforeach
                </select>

                <div class="modal-actions">
                    <button class="outline-button" type="button" id="cancel-delete-table-modal">
                        <img src="{{ asset('images/icon/Icon Hapus.png') }}" alt="" class="button-icon">
                        Batal
                    </button>
                    <button class="danger-button" type="submit">
                        <img src="{{ asset('images/icon/Icon Hapus.png') }}" alt="" class="button-icon invert-icon">
                        Hapus Meja
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>
@endsection
