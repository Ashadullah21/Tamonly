@extends('layouts.admin')

@section('title', isset($movie) ? 'Edit Movie: ' . $movie->title : 'Add New Movie')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.movies.index') }}" class="text-decoration-none text-muted">
        <i class="bi bi-arrow-left me-1"></i> Back to Movies
    </a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 border-0">
        <h5 class="mb-0 fw-bold">{{ isset($movie) ? 'Edit Movie Details' : 'New Movie Details' }}</h5>
    </div>
    <div class="card-body p-4 pt-0">
        <form action="{{ isset($movie) ? route('admin.movies.update', $movie) : route('admin.movies.store') }}" method="POST">
            @csrf
            @if(isset($movie))
                @method('PUT')
            @endif

            <div class="row g-3">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Movie Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $movie->title ?? '') }}" required placeholder="e.g. The Avengers">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description / Synopsis</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="5" placeholder="Enter movie synopsis...">{{ old('description', $movie->description ?? '') }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Genres</label>
                            <div class="d-flex flex-wrap gap-2 p-2 border rounded bg-light" style="max-height: 160px; overflow-y: auto;">
                                @php $selectedGenres = old('genres', isset($movie) ? $movie->genres->pluck('id')->toArray() : []); @endphp
                                @foreach($genres as $genre)
                                    <div class="form-check form-check-inline m-0">
                                        <input class="form-check-input" type="checkbox" name="genres[]" value="{{ $genre->id }}" id="genre_{{ $genre->id }}" {{ in_array($genre->id, $selectedGenres) ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="genre_{{ $genre->id }}">{{ $genre->name }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Languages</label>
                            <div class="d-flex flex-wrap gap-2 p-2 border rounded bg-light" style="max-height: 160px; overflow-y: auto;">
                                @php $selectedLangs = old('languages', isset($movie) ? $movie->languages->pluck('id')->toArray() : []); @endphp
                                @foreach($languages as $lang)
                                    <div class="form-check form-check-inline m-0">
                                        <input class="form-check-input" type="checkbox" name="languages[]" value="{{ $lang->id }}" id="lang_{{ $lang->id }}" {{ in_array($lang->id, $selectedLangs) ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="lang_{{ $lang->id }}">{{ $lang->name }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                            <option value="active" {{ old('status', $movie->status ?? '') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $movie->status ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Release Year</label>
                        <input type="number" name="release_year" class="form-control @error('release_year') is-invalid @enderror" value="{{ old('release_year', $movie->release_year ?? '') }}" placeholder="e.g. 2026">
                        @error('release_year') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Runtime (minutes)</label>
                        <input type="number" name="runtime" class="form-control @error('runtime') is-invalid @enderror" value="{{ old('runtime', $movie->runtime ?? '') }}" placeholder="e.g. 143">
                        @error('runtime') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-check-lg me-1"></i> {{ isset($movie) ? 'Update Movie' : 'Save Movie' }}
                </button>
                <a href="{{ route('admin.movies.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
            </div>
        </form>
    </div>
</div>

@if(isset($movie))
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white p-4 pb-0 border-0 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">Download Links & Qualities</h5>
        <span class="badge bg-primary bg-opacity-10 text-primary">{{ $movie->links->count() }} Available</span>
    </div>
    <div class="card-body p-4 pt-3">
        <form action="{{ route('admin.movies.links.store', $movie) }}" method="POST" class="mb-4 p-3 bg-light rounded">
            @csrf
            <h6 class="fw-bold mb-3">Add Quality Download Link</h6>
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Quality <span class="text-danger">*</span></label>
                    <input type="text" name="quality" class="form-control" placeholder="1080p" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Resolution</label>
                    <input type="text" name="resolution" class="form-control" placeholder="1920x1080">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">File Size</label>
                    <input type="text" name="file_size" class="form-control" placeholder="2.4 GB">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Download URL <span class="text-danger">*</span></label>
                    <input type="url" name="download_url" class="form-control" placeholder="https://..." required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-plus-lg me-1"></i> Add Link
                    </button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Quality</th>
                        <th>Resolution</th>
                        <th>Size</th>
                        <th>URL</th>
                        <th class="pe-3 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movie->links as $link)
                    <tr>
                        <td class="ps-3 fw-bold">{{ $link->quality }}</td>
                        <td><span class="badge bg-secondary bg-opacity-10 text-dark">{{ $link->resolution ?? 'N/A' }}</span></td>
                        <td>{{ $link->file_size ?? 'N/A' }}</td>
                        <td>
                            <a href="{{ $link->download_url }}" target="_blank" class="text-truncate d-inline-block text-decoration-none" style="max-width:240px;">
                                <i class="bi bi-link-45deg me-1"></i>{{ $link->download_url }}
                            </a>
                        </td>
                        <td class="pe-3 text-end">
                            <form action="{{ route('admin.movies.links.destroy', [$movie, $link]) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this link?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-4 text-muted">No download links added yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
