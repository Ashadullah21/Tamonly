@extends('layouts.app')

@section('title', 'System Error')

@section('content')
<div class="container py-5 text-center min-vh-75 d-flex align-items-center justify-content-center">
    <div class="empty-state-card p-5">
        <div class="empty-icon mb-4">
            <span class="display-1 fw-bold text-danger opacity-75">500</span>
        </div>
        <h2 class="fw-bold mb-3">Something Went Wrong</h2>
        <p class="text-muted lead mb-4">
            An unexpected error occurred while processing your request. Please try again shortly.
        </p>
        <div class="d-flex justify-content-center gap-3">
            <a href="{{ route('home') }}" class="btn btn-primary px-4 py-2 rounded-pill">
                <i class="bi bi-house me-2"></i> Go to Homepage
            </a>
            <a href="{{ route('movies.index') }}" class="btn btn-outline-light px-4 py-2 rounded-pill">
                <i class="bi bi-collection me-2"></i> Browse Movies
            </a>
        </div>
    </div>
</div>
@endsection
