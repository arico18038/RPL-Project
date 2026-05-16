@extends('layouts.app')

@section('title', 'Login Admin - 4SR')
@section('body_class', 'login-body')

@section('content')
<main class="login-page">
    <section class="login-form-panel">
        <div class="login-form-wrap">
            <h1>Selamat Kembali Admin!</h1>

            @if ($errors->any())
                <div class="alert error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="login-form">
                @csrf

                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="Example@email.com" required autofocus>

                <label for="password">Password</label>
                <input id="password" name="password" type="password" placeholder="at least 8 characters" required>

                <a href="{{ route('pos.index') }}" class="forgot-link">Forgot Password?</a>

                <button type="submit">Sign in</button>
            </form>

            <p class="signup-text">Don't you have an account? <a href="{{ route('pos.index') }}">Sign up</a></p>
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
