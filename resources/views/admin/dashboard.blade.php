@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h6 class="text-muted mb-2">Total Movies</h6>
                <h3 class="mb-0">{{ $stats['total_movies'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h6 class="text-muted mb-2">Active Links</h6>
                <h3 class="mb-0">{{ $stats['total_links'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h6 class="text-muted mb-2">Total Downloads</h6>
                <h3 class="mb-0">{{ $stats['total_downloads'] }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white p-4 pb-0 border-0">
        <h5 class="mb-0">Recently Added Movies</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Title</th>
                        <th>Year</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="pe-4 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stats['recent_movies'] as $movie)
                    <tr>
                        <td class="ps-4">{{ $movie->title }}</td>
                        <td>{{ $movie->release_year }}</td>
                        <td>
                            <span class="badge bg-{{ $movie->status == 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($movie->status) }}
                            </span>
                        </td>
                        <td>{{ $movie->created_at->format('M d, Y') }}</td>
                        <td class="pe-4 text-end">
                            <a href="{{ route('admin.movies.edit', $movie) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No movies found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
