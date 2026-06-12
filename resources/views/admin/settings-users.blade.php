@extends('layouts.app')

@section('title', 'Pengaturan Pengguna - Sikasir-4SR')
@section('body_class', 'admin-body')

@section('content')
@include('partials.sidebar', ['active' => 'pengaturan'])

<main class="main-content">
    @include('partials.topbar', ['title' => 'Pengguna', 'subtitle' => 'Kelola akses pengguna sistem', 'role' => 'Kasir'])

    <section class="admin-panel settings-panel">
        <div class="settings-tabs">
            <a href="{{ route('admin.settings') }}">Profil Toko</a>
            <a href="{{ route('admin.settings.about') }}">Tentang Kami</a>
            <a href="{{ route('admin.settings.receipt') }}">Struk & Pajak</a>
            <a class="active" href="{{ route('admin.settings.users') }}">Pengguna</a>
            <a href="{{ route('admin.settings.data') }}">Data</a>
        </div>

        <div class="settings-card">
            <h2>Akun Aktif</h2>
            <div class="settings-list">
                <div>
                    <strong>{{ auth()->user()?->name ?? 'Admin Cash-Dig' }}</strong>
                    <span>{{ auth()->user()?->email ?? 'admin@cashdig.local' }}</span>
                </div>
                <span class="badge completed">Admin</span>
            </div>
            <button class="outline-button" type="button">Tambah Pengguna</button>
        </div>
    </section>
</main>
@endsection
