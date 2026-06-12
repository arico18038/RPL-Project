@extends('layouts.app')

@section('title', 'Sign Up Admin - 4SR')
@section('body_class', 'login-body')

@section('content')
<main class="login-page">
    <section class="login-form-panel">
        <div class="login-form-wrap">
            <h1>Buat Akun Admin</h1>

            @if ($errors->any())
                <div class="alert error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('register.store') }}" class="login-form">
                @csrf

                <label for="name">Nama</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Nama admin" required autofocus>

                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="Example@email.com" required>

                <label for="password">Password</label>
                <input id="password" name="password" type="password" placeholder="at least 8 characters" required>

                <label for="password_confirmation">Konfirmasi Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" placeholder="ulangi password" required>

                <button type="submit">Sign up</button>
            </form>

            <p class="signup-text">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
        </div>
    </section>

    <section class="login-brand-panel" aria-label="Brand Rumah Makan 4SR">
        <img src="{{ asset('assets/logo-4sr.png') }}" alt="Logo 4SR">
        <h2>4SR</h2>
        <strong>RUMAH MAKAN</strong>
        <span>SEJAK 2005</span>
    </section>
</main>
@endsection
