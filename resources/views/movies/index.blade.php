@extends('layouts.app')

@section('title', request('search') ? 'Search: ' . request('search') : 'Browse Movies')

@section('content')
<div class="container py-4 py-md-5">
    @if(request('search'))
        <!-- Prominent Search Results Header -->
        <div class="search-results-header">
            <div class="search-title-wrap">
                <h1 class="search-title">
                    Search results for <span class="query-highlight">"{{ request('search') }}"</span>
                </h1>
                <span class="results-count-badge">
                    {{ $movies->total() }} {{ Str::plural('movie', $movies->total()) }} found
                </span>
            </div>

            <a href="{{ route('movies.index') }}" class="clear-search-btn">
                <i class="bi bi-x-circle"></i> Clear Search
            </a>
        </div>
    @else
        <!-- Catalog Header -->
        <div class="section-header mb-4">
            <div>
                <h1 class="section-title mb-1">
                    <i class="bi bi-collection-play"></i> Full Movie Catalog
                </h1>
                <p class="text-muted small mb-0">Showing {{ $movies->total() }} verified titles available in the vault</p>
            </div>
        </div>
    @endif

    <!-- Search / Filter Bar on Index -->
    <div class="mb-4">
        <form action="{{ route('movies.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-6 col-lg-5">
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input
                        type="search"
                        name="search"
                        class="form-control bg-dark text-light border-secondary"
                        placeholder="Search another movie..."
                        value="{{ request('search') }}"
                        aria-label="Search"
                    >
                    <button class="btn btn-primary px-3" type="submit">Filter</button>
                </div>
            </div>
            @if(request('search'))
            <div class="col-auto">
                <a href="{{ route('movies.index') }}" class="btn btn-outline-light btn-sm">
                    Reset
                </a>
            </div>
            @endif
        </form>
    </div>

    <!-- Movies Grid -->
    <div class="row g-2 g-sm-3 g-md-4 mb-4 mb-md-5">
        @forelse($movies as $movie)
        <div class="col-6 col-md-4 col-lg-3 col-xl-2">
            @include('movies.partials.card', ['movie' => $movie])
        </div>
        @empty
        <!-- Empty State UI -->
        <div class="col-12">
            <div class="empty-state-card">
                <div class="empty-icon">
                    <i class="bi bi-search"></i>
                </div>
                <h2 class="empty-title">No movies found</h2>
                <p class="empty-desc">
                    We couldn't find a movie matching <strong>"{{ request('search') }}"</strong>.<br>
                    Try another search term, check spelling, or browse our latest releases.
                </p>
                <div class="empty-actions">
                    <a href="{{ route('movies.index') }}" class="btn btn-primary px-4 py-2 rounded-pill">
                        <i class="bi bi-collection me-1"></i> Browse All Movies
                    </a>
                    <a href="{{ route('home') }}" class="btn btn-outline-light px-4 py-2 rounded-pill">
                        <i class="bi bi-house me-1"></i> Go Home
                    </a>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($movies->hasPages())
    <div class="d-flex justify-content-center pt-2 overflow-auto" style="-webkit-overflow-scrolling: touch;">
        {{ $movies->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
