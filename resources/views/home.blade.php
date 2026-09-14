@extends('layouts.app')

@section('title', 'MovieVault — Personal Movie Index & Direct Downloads')

@section('content')
<!-- Hero Search Section -->
<section class="hero-search-wrapper">
    <div class="container">
        <h1 class="hero-heading">
            Your Personal <span class="text-gradient">Movie Vault</span>
        </h1>
        <p class="hero-subheading">
            Streamlined, high-speed access to authorized movies in Ultra HD, 1080p, 720p, and 480p.
        </p>

        <div class="search-box-container">
            <form action="{{ route('movies.index') }}" method="GET" class="hero-search-form">
                <span class="search-icon">
                    <i class="bi bi-search"></i>
                </span>
                <input
                    type="search"
                    name="search"
                    class="search-input"
                    placeholder="Search movies by title, year, or franchise..."
                    required
                    autocomplete="off"
                    aria-label="Search movies"
                >
                <button type="submit" class="btn btn-primary search-submit-btn">
                    <i class="bi bi-search me-1"></i> Search
                </button>
            </form>

            <!-- Quick Filter Suggestions -->
            <div class="popular-tags">
                <span class="tags-label">Popular Searches:</span>
                <a href="{{ route('movies.index', ['search' => 'The Avengers']) }}" class="tag-chip">The Avengers</a>
                <a href="{{ route('movies.index', ['search' => 'Mankatha']) }}" class="tag-chip">Mankatha</a>
                <a href="{{ route('movies.index', ['search' => 'Coolie']) }}" class="tag-chip">Coolie</a>
                <a href="{{ route('movies.index', ['search' => 'Avengers: End Game']) }}" class="tag-chip">Avengers: End Game</a>
            </div>
        </div>
    </div>
</section>

<!-- Content Catalog Grid -->
<div class="container py-5">
    <!-- Popular Movies Section -->
    @if($popular->isNotEmpty())
    <div class="mb-5">
        <div class="section-header">
            <h2 class="section-title">
                <i class="bi bi-fire"></i> Popular Right Now
            </h2>
            <a href="{{ route('movies.index') }}" class="section-link">
                View All <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="row g-4">
            @foreach($popular as $movie)
            <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                @include('movies.partials.card', ['movie' => $movie])
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Recently Added Section -->
    <div>
        <div class="section-header">
            <h2 class="section-title">
                <i class="bi bi-clock-history"></i> Recently Added
            </h2>
            <a href="{{ route('movies.index') }}" class="section-link">
                Browse Full Catalog <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="row g-4">
            @foreach($latest as $movie)
            <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                @include('movies.partials.card', ['movie' => $movie])
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
