<header class="top-header">
    <div>
        <h1>{{ $title }}</h1>
        <p>{{ $subtitle }}</p>
    </div>

    <div class="header-actions">
        <div class="time-card">
            <span>Waktu</span>
            <strong id="jam-sekarang">--:--:-- WIB</strong>
        </div>

        <div class="user-card">
            <div class="avatar">AH</div>
            <div>
                <h4>Akbar Hidayat</h4>
                <p>{{ $role ?? 'Kasir' }}</p>
            </div>
        </div>
    </div>
</header>
