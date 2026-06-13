@extends('layouts.app')

@section('title', 'Pengaturan Profil Toko - Sikasir-4SR')
@section('body_class', 'admin-body')

@section('content')
@include('partials.sidebar', ['active' => 'pengaturan'])

<main class="main-content">
    @include('partials.topbar', ['title' => 'Pengaturan Toko', 'subtitle' => 'Konfigurasi profil toko', 'role' => 'Kasir'])

    @if (session('success'))
        <div class="toast-message is-visible">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert error">{{ $errors->first() }}</div>
    @endif

    <section class="admin-panel settings-panel">
        <div class="settings-tabs">
            <a class="active" href="{{ route('admin.settings') }}">
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
            <a href="{{ route('admin.settings.data') }}">
                <img src="{{ asset('images/icon/Laporan.png') }}" alt="" class="button-icon">
                Data
            </a>
        </div>

        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="settings-card" id="profil-toko">
            @csrf
            @method('PUT')

            <h2>Informasi Toko</h2>

            <label for="store_name">Nama toko <b>*</b></label>
            <input id="store_name" name="store_name" type="text" placeholder="Contoh: Rumah Makan 4SR" value="{{ old('store_name', $profile['store_name']) }}" required>

            <label for="store_address">Alamat lengkap</label>
            <textarea id="store_address" name="store_address" placeholder="Contoh: Jl. Merdeka Kendala, KM.14.C">{{ old('store_address', $profile['store_address']) }}</textarea>

            <div class="form-grid">
                <div>
                    <label for="store_phone">Nomor telepon</label>
                    <input id="store_phone" name="store_phone" type="text" placeholder="+62" value="{{ old('store_phone', $profile['store_phone']) }}">
                </div>
                <div>
                    <label for="store_whatsapp">Nomor WhatsApp</label>
                    <input id="store_whatsapp" name="store_whatsapp" type="text" placeholder="+62" value="{{ old('store_whatsapp', $profile['store_whatsapp']) }}">
                </div>
            </div>

            <label for="store_npwp">NPWP (opsional)</label>
            <input id="store_npwp" name="store_npwp" type="text" placeholder="Contoh: 01.000.000.000" value="{{ old('store_npwp', $profile['store_npwp']) }}">

            <label for="store_logo_file">Logo toko (opsional)</label>
            <div class="upload-row">
                <label class="upload-box" for="store_logo_file">
                    @if (!empty($profile['store_logo']))
                        <img src="{{ asset($profile['store_logo']) }}" alt="Logo toko">
                    @else
                        Klik untuk upload
                    @endif
                </label>
                <input id="store_logo_file" name="store_logo_file" type="file" accept="image/*">
                <p>Format: JPG, PNG, atau SVG<br>Ukuran maksimal: 2MB</p>
            </div>

            <div class="settings-actions">
                <button class="primary-button" type="submit">
                    <img src="{{ asset('images/icon/icon-save.png') }}" alt="" class="button-icon invert-icon">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </section>
</main>
@endsection
