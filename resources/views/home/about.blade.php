@extends('layouts.app')

@section('title', 'Tentang Kami - Sikasir-4SR')
@section('body_class', 'pos-body')

@section('content')
@include('partials.sidebar', ['active' => 'tentang'])

<main class="main-content">
    @include('partials.topbar', ['title' => 'Tentang Kami', 'subtitle' => $profile['store_name'] ?? 'Rumah Makan 4SR', 'role' => auth()->check() ? 'Admin' : 'Pengunjung'])

    <section class="about-panel">
        <div class="store-profile-public">
            <div class="store-profile-logo">
                <img src="{{ asset($profile['store_logo'] ?? 'assets/logo-4sr.png') }}" alt="Logo {{ $profile['store_name'] ?? 'Rumah Makan 4SR' }}">
            </div>
            <div>
                <p class="public-kicker">Profil Toko</p>
                <h1>{{ $profile['store_name'] ?? 'Rumah Makan 4SR' }}</h1>
                @if (!empty($profile['store_address']))
                    <p>{{ $profile['store_address'] }}</p>
                @else
                    <p>Profil toko dapat diperbarui melalui dashboard admin pada menu Pengaturan.</p>
                @endif

                <div class="store-profile-grid">
                    <article>
                        <span><img src="{{ asset('images/icon/Aksi.png') }}" alt="" class="inline-icon"> Telepon</span>
                        <strong>{{ $profile['store_phone'] ?: '-' }}</strong>
                    </article>
                    <article>
                        <span><img src="{{ asset('images/icon/Mint.png') }}" alt="" class="inline-icon"> WhatsApp</span>
                        <strong>{{ $profile['store_whatsapp'] ?: '-' }}</strong>
                    </article>
                    <article>
                        <span><img src="{{ asset('images/icon/Laporan.png') }}" alt="" class="inline-icon"> NPWP</span>
                        <strong>{{ $profile['store_npwp'] ?: '-' }}</strong>
                    </article>
                </div>
            </div>
        </div>

        <div class="about-divider"></div>

        <p class="public-kicker">{{ $about['about_kicker'] }}</p>
        <h1>{{ $about['about_title'] }}</h1>
        <p>{{ $about['about_description'] }}</p>

        <div class="about-grid">
            @for ($index = 1; $index <= 3; $index++)
                <article>
                    <h2>{{ $about["about_feature_{$index}_title"] }}</h2>
                    <p>{{ $about["about_feature_{$index}_text"] }}</p>
                </article>
            @endfor
        </div>
    </section>
</main>
@endsection
