@extends('layouts.app')

@section('title', 'Pengaturan - Sikasir-4SR')
@section('body_class', 'admin-body')

@section('content')
@include('partials.sidebar', ['active' => 'pengaturan'])

<main class="main-content">
    @include('partials.topbar', ['title' => 'Pengaturan Toko', 'subtitle' => 'Konfigurasi sistem dan preferensi toko', 'role' => 'Kasir'])

    <section class="admin-panel settings-panel">
        <div class="settings-tabs">
            <button class="active" type="button">Profil Toko</button>
            <button type="button">Struk & Pajak</button>
            <button type="button">Pengguna</button>
            <button type="button">Data</button>
        </div>

        <div class="settings-card">
            <h2>Informasi Toko</h2>

            <label>Nama toko <b>*</b></label>
            <input type="text" placeholder="Contoh: Sumber Rezeki 5" value="Rumah Makan 4SR">

            <label>Alamat lengkap <b>*</b></label>
            <textarea placeholder="Contoh: Jl. Merdeka Kendala, KM.14.C"></textarea>

            <div class="form-grid">
                <div>
                    <label>Nomor telepon <b>*</b></label>
                    <input type="text" placeholder="+62">
                </div>
                <div>
                    <label>Nomor WhatsApp <b>*</b></label>
                    <input type="text" placeholder="+62">
                </div>
            </div>

            <label>NPWP (opsional)</label>
            <input type="text" placeholder="Contoh: 01.000.000.000">

            <label>Logo toko (opsional)</label>
            <div class="upload-row">
                <div class="upload-box">Klik untuk upload</div>
                <p>Format: JPG, PNG, atau SVG<br>Ukuran maksimal: 2MB</p>
            </div>
        </div>

        <button class="disabled-button" type="button">Simpan Perubahan</button>
    </section>
</main>
@endsection
