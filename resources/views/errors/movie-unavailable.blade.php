@extends('layouts.app')

@section('title', 'Movie Unavailable')

@section('content')
<div class="container py-5 text-center min-vh-75 d-flex align-items-center justify-content-center">
    <div class="empty-state-card p-5">
        <div class="empty-icon mb-4">
            <i class="bi bi-film text-warning" style="font-size: 3.5rem;"></i>
        </div>
        <h2 class="fw-bold mb-3">Movie Unavailable</h2>
        <p class="text-muted lead mb-4">
            {{ $message ?? 'This movie is currently unavailable or has been archived.' }}
        </p>
        <div class="d-flex justify-content-center gap-3">
            <a href="{{ route('movies.index') }}" class="btn btn-primary px-4 py-2 rounded-pill">
                <i class="bi bi-collection-play me-2"></i> Browse All Movies
            </a>
            <a href="{{ route('home') }}" class="btn btn-outline-light px-4 py-2 rounded-pill">
                <i class="bi bi-house me-2"></i> Back to Home
            </a>
        </div>
    </div>
</div>
@endsection
