<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'MovieVault')) — Cinema & Movie Index</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Vite Assets -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
    <!-- Top Sticky Navbar -->
    <nav class="navbar navbar-expand-lg site-navbar sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="bi bi-play-circle-fill brand-icon"></i>
                <span class="brand-text">{{ config('app.name', 'MovieVault') }}</span>
                <span class="brand-tag">HD</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                            <i class="bi bi-house-door me-1"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('movies.index') ? 'active' : '' }}" href="{{ route('movies.index') }}">
                            <i class="bi bi-grid me-1"></i> Browse Catalog
                        </a>
                    </li>
                </ul>

                <!-- Header Search -->
                <form class="nav-search d-flex" action="{{ route('movies.index') }}" method="GET">
                    <input class="nav-search-input" type="search" name="search" placeholder="Search movies..." value="{{ request('search') }}" aria-label="Search">
                    <button class="nav-search-btn" type="submit" title="Search">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="row align-items-center justify-content-between gy-3">
                <div class="col-md-6 text-center text-md-start">
                    <a href="{{ route('home') }}" class="footer-brand d-inline-flex align-items-center gap-2">
                        <i class="bi bi-play-circle-fill text-primary"></i> {{ config('app.name', 'MovieVault') }}
                    </a>
                    <p class="footer-text mb-0">
                        Fast, high-quality, authorized movie catalog index. All titles and links verified.
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <div class="d-flex justify-content-center justify-content-md-end gap-3 small text-muted">
                        <a href="{{ route('movies.index') }}" class="text-decoration-none text-muted">Browse All</a>
                        <span>·</span>
                        <a href="{{ route('login') }}" class="text-decoration-none text-muted">Admin Portal</a>
                    </div>
                    <div class="text-dim small mt-2">
                        &copy; {{ date('Y') }} {{ config('app.name', 'MovieVault') }}. All rights reserved.
                    </div>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
