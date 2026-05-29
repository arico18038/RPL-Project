@extends('layouts.app')

@section('title', 'Pengaturan Tentang Kami - Sikasir-4SR')
@section('body_class', 'admin-body')

@section('content')
@include('partials.sidebar', ['active' => 'pengaturan'])

<main class="main-content">
    @include('partials.topbar', ['title' => 'Tentang Kami', 'subtitle' => 'Konten yang tampil pada sisi pelanggan', 'role' => 'Kasir'])

    @if (session('success'))
        <div class="toast-message is-visible">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert error">{{ $errors->first() }}</div>
    @endif

    <section class="admin-panel settings-panel">
        <div class="settings-tabs">
            <a href="{{ route('admin.settings') }}">Profil Toko</a>
            <a class="active" href="{{ route('admin.settings.about') }}">Tentang Kami</a>
            <button type="button">Struk & Pajak</button>
            <button type="button">Pengguna</button>
            <button type="button">Data</button>
        </div>

        <form method="POST" action="{{ route('admin.settings.about.update') }}" class="settings-card about-settings-card">
            @csrf
            @method('PUT')

            <div class="settings-card-header">
                <div>
                    <h2>Konten Tentang Kami</h2>
                    <p>Bagian ini akan tampil pada halaman pelanggan.</p>
                </div>
                <a class="outline-link" href="{{ route('about') }}" target="_blank">Lihat Halaman</a>
            </div>

            <label for="about_kicker">Label kecil <b>*</b></label>
            <input id="about_kicker" name="about_kicker" type="text" value="{{ old('about_kicker', $about['about_kicker']) }}" required>

            <label for="about_title">Judul utama <b>*</b></label>
            <input id="about_title" name="about_title" type="text" value="{{ old('about_title', $about['about_title']) }}" required>

            <label for="about_description">Deskripsi utama <b>*</b></label>
            <textarea id="about_description" name="about_description" required>{{ old('about_description', $about['about_description']) }}</textarea>

            <div class="settings-feature-grid">
                @for ($index = 1; $index <= 3; $index++)
                    <div class="settings-feature-card">
                        <h3>Fitur {{ $index }}</h3>

                        <label for="about_feature_{{ $index }}_title">Judul fitur <b>*</b></label>
                        <input
                            id="about_feature_{{ $index }}_title"
                            name="about_feature_{{ $index }}_title"
                            type="text"
                            value="{{ old("about_feature_{$index}_title", $about["about_feature_{$index}_title"]) }}"
                            required
                        >

                        <label for="about_feature_{{ $index }}_text">Deskripsi fitur <b>*</b></label>
                        <textarea id="about_feature_{{ $index }}_text" name="about_feature_{{ $index }}_text" required>{{ old("about_feature_{$index}_text", $about["about_feature_{$index}_text"]) }}</textarea>
                    </div>
                @endfor
            </div>

            <div class="settings-actions">
                <button class="primary-button" type="submit">Simpan Tentang Kami</button>
            </div>
        </form>
    </section>
</main>
@endsection
