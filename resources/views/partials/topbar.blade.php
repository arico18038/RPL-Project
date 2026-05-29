<header class="top-header">
    <div>
        <h1>{{ $title }}</h1>
        <p>{{ $subtitle }}</p>
    </div>

    <div class="header-actions">
        <div class="time-card">
            <span>
                <img src="{{ asset('images/icon/Kalender.png') }}" alt="" class="inline-icon">
                Waktu
            </span>
            <strong id="jam-sekarang">--:--:-- WIB</strong>
        </div>

        @guest
            <a href="{{ route('login') }}" class="user-card profile-login-link" title="Login Admin">
                <div class="avatar">PG</div>
                <div>
                    <h4>Pengunjung</h4>
                    <p>Pengunjung</p>
                </div>
            </a>
        @else
            <div class="user-card">
                <div class="avatar">AD</div>
                <div>
                    <h4>{{ auth()->user()?->name ?? 'Admin' }}</h4>
                    <p>{{ $role ?? 'Admin' }}</p>
                </div>
            </div>
        @endguest
    </div>
</header>
