@extends('layouts.app')

@section('title', 'Tentang Kami - Sikasir-4SR')
@section('body_class', 'pos-body')

@section('content')
@include('partials.sidebar', ['active' => 'tentang'])

<main class="main-content">
    @include('partials.topbar', ['title' => 'Tentang Kami', 'subtitle' => 'Rumah Makan 4SR', 'role' => auth()->check() ? 'Admin' : 'Pengunjung'])

    <section class="about-panel">
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
