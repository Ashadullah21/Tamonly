<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin - {{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts and Styles -->
    @vite(['resources/sass/admin.scss', 'resources/js/app.js'])
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar d-flex flex-column">
            <div class="p-4 border-bottom border-secondary border-opacity-25">
                <h4 class="mb-0 text-white">{{ config('app.name', 'Laravel') }} Admin</h4>
            </div>
            
            <ul class="nav flex-column mt-3 mb-auto">
                <li class="nav-item">
                    <a href="/admin" class="nav-link {{ request()->is('admin') ? 'active' : '' }}">
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/movies" class="nav-link {{ request()->is('admin/movies*') ? 'active' : '' }}">
                        Movies
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/genres" class="nav-link {{ request()->is('admin/genres*') ? 'active' : '' }}">
                        Genres
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/languages" class="nav-link {{ request()->is('admin/languages*') ? 'active' : '' }}">
                        Languages
                    </a>
                </li>
            </ul>

            <div class="p-3 border-top border-secondary border-opacity-25">
                <a href="/" class="btn btn-outline-light w-100" target="_blank">View Site</a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-4 py-3">
                <div class="container-fluid">
                    <span class="navbar-brand mb-0 h5">@yield('title', 'Dashboard')</span>
                    
                    <div class="d-flex align-items-center">
                        <span class="me-3">{{ Auth::user()->name ?? 'Admin' }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-danger">Logout</button>
                        </form>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <div class="admin-content">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
