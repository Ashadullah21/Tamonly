@extends('layouts.app')

@section('title', 'Page Not Found')

@section('content')
<div class="container py-5 text-center min-vh-75 d-flex align-items-center justify-content-center">
    <div class="empty-state-card p-5">
        <div class="empty-icon mb-4">
            <span class="display-1 fw-bold text-primary opacity-75">404</span>
        </div>
        <h2 class="fw-bold mb-3">Page Not Found</h2>
        <p class="text-muted lead mb-4">
            The page or movie you are looking for does not exist or has moved.
        </p>
        <div class="d-flex justify-content-center gap-3">
            <a href="{{ route('home') }}" class="btn btn-primary px-4 py-2 rounded-pill">
                <i class="bi bi-house me-2"></i> Go to Homepage
            </a>
            <a href="{{ route('movies.index') }}" class="btn btn-outline-light px-4 py-2 rounded-pill">
                <i class="bi bi-search me-2"></i> Search Movies
            </a>
        </div>
    </div>
</div>
@endsection
