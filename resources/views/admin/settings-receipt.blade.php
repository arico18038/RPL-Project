@extends('layouts.app')

@section('title', 'Pengaturan Diskon & Pajak - Sikasir-4SR')
@section('body_class', 'admin-body')

@section('content')
@include('partials.sidebar', ['active' => 'pengaturan'])

<main class="main-content">
    @include('partials.topbar', ['title' => 'Diskon & Pajak', 'subtitle' => 'Atur diskon otomatis dan nilai pajak', 'role' => 'Kasir'])

    @if (session('success'))
        <div class="toast-message is-visible">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert error">{{ $errors->first() }}</div>
    @endif

    <section class="admin-panel settings-panel">
        <div class="settings-tabs">
            <a href="{{ route('admin.settings') }}">Profil Toko</a>
            <a href="{{ route('admin.settings.about') }}">Tentang Kami</a>
            <a class="active" href="{{ route('admin.settings.receipt') }}">Diskon & Pajak</a>
            <a href="{{ route('admin.settings.data') }}">Data</a>
        </div>

        <form method="POST" action="{{ route('admin.settings.receipt.update') }}" class="settings-card">
            @csrf
            @method('PUT')

            <h2>Pengaturan Pajak</h2>
            <div class="form-grid">
                <div>
                    <label for="tax_rate">PPN (%)</label>
                    <input id="tax_rate" name="tax_rate" type="number" value="{{ old('tax_rate', $salesSettings['tax_rate']) }}" min="0" max="100" step="0.1">
                </div>
                <div>
                    <label for="payment_method">Metode pembayaran</label>
                    <select id="payment_method" disabled>
                        <option>Tunai</option>
                    </select>
                </div>
            </div>

            <h2>Pengaturan Diskon</h2>
            <label class="inline-check">
                <input type="checkbox" name="discount_enabled" value="1" @checked(old('discount_enabled', $salesSettings['discount_enabled']) === '1')>
                Aktifkan diskon otomatis
            </label>

            <div class="form-grid">
                <div>
                    <label for="discount_type">Jenis diskon</label>
                    <select id="discount_type" name="discount_type">
                        <option value="persen" @selected(old('discount_type', $salesSettings['discount_type']) === 'persen')>Persen (%)</option>
                        <option value="rupiah" @selected(old('discount_type', $salesSettings['discount_type']) === 'rupiah')>Nominal (Rp)</option>
                    </select>
                </div>
                <div>
                    <label for="discount_value">Nilai diskon</label>
                    <input id="discount_value" name="discount_value" type="number" value="{{ old('discount_value', $salesSettings['discount_value']) }}" min="0" step="1">
                </div>
            </div>

            <div class="settings-actions">
                <button class="primary-button" type="submit">Simpan Pengaturan</button>
            </div>
        </form>
    </section>
</main>
@endsection
