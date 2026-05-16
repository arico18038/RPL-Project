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
            <button class="outline-button" type="button">Ekspor Excel</button>
            <button class="primary-button" type="button" id="open-menu-modal">+ Tambah Barang</button>
        </div>

        @if ($lowStockCount > 0)
            <div class="stock-alert">{{ $lowStockCount }} barang memiliki stok di bawah minimum</div>
        @endif

        <div class="table-filters">
            <select>
                <option>Semua kategori</option>
                @foreach ($categories as $category)
                    <option>{{ $category->name }}</option>
                @endforeach
            </select>

            <div class="search-box table-search">
                <span class="search-icon">Cari</span>
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
                            <td>{{ str_contains(strtolower($menu->category?->name ?? ''), 'minuman') ? 'Botol' : 'Pcs' }}</td>
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
                                        data-description="{{ $menu->description }}"
                                        data-image-url="{{ $menu->image_url }}"
                                        data-is-available="{{ $menu->is_available ? '1' : '0' }}"
                                    >Edit</button>
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

        <p class="table-caption">Menampilkan 1 - {{ $menus->count() }} dari {{ $menus->count() }} data</p>
    </section>

    <div class="modal-backdrop" id="menu-modal" hidden>
        <div class="modal-card">
            <div class="modal-header">
                <h2 id="menu-modal-title">Tambah Barang Baru</h2>
                <button type="button" id="close-menu-modal" aria-label="Tutup">x</button>
            </div>

            <form method="POST" action="{{ route('admin.menu.store') }}" class="menu-form" id="menu-form" data-store-url="{{ route('admin.menu.store') }}">
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
                </div>

                <label for="description">Deskripsi (opsional)</label>
                <textarea id="description" name="description" placeholder="Deskripsi singkat barang">{{ old('description') }}</textarea>

                <label for="image_url">URL gambar produk (opsional)</label>
                <input id="image_url" name="image_url" type="url" placeholder="https://..." value="{{ old('image_url') }}">

                <label for="is_available">Status aktif *</label>
                <select id="is_available" name="is_available" required>
                    <option value="1" @selected(old('is_available', '1') === '1')>Aktif</option>
                    <option value="0" @selected(old('is_available') === '0')>Nonaktif</option>
                </select>

                <div class="modal-actions">
                    <button class="danger-button" type="submit" form="delete-menu-form" id="delete-menu-button" hidden>Hapus Barang</button>
                    <button class="outline-button" type="button" id="cancel-menu-modal">Batal</button>
                    <button class="primary-button" type="submit">Simpan</button>
                </div>
            </form>

            <form method="POST" action="#" id="delete-menu-form" hidden>
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</main>
@endsection
