@extends('layouts.admin')

@section('title', 'Movies Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Movies</h4>
    <a href="{{ route('admin.movies.create') }}" class="btn btn-primary">Add New Movie</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Title</th>
                        <th>Year</th>
                        <th>Status</th>
                        <th>Views</th>
                        <th>Created At</th>
                        <th class="pe-4 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movies as $movie)
                    <tr>
                        <td class="ps-4">
                            <strong>{{ $movie->title }}</strong>
                            <div class="text-muted small">{{ $movie->slug }}</div>
                        </td>
                        <td>{{ $movie->release_year ?? '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $movie->status == 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($movie->status) }}
                            </span>
                        </td>
                        <td>{{ $movie->view_count }}</td>
                        <td>{{ $movie->created_at->format('M d, Y') }}</td>
                        <td class="pe-4 text-end">
                            <a href="{{ route('admin.movies.edit', $movie) }}" class="btn btn-sm btn-outline-primary me-2">Edit</a>
                            <form action="{{ route('admin.movies.destroy', $movie) }}" method="POST" class="d-inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this movie?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-film d-block fs-1 mb-3"></i>
                            No movies found. <a href="{{ route('admin.movies.create') }}">Create one now</a>.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($movies->hasPages())
    <div class="card-footer bg-white p-4">
        {{ $movies->links() }}
    </div>
    @endif
</div>
@endsection
