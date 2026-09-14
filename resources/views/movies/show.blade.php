@extends('layouts.app')

@section('title', $movie->clean_title . ($movie->release_year ? ' (' . $movie->release_year . ')' : ''))

@section('content')
<div class="movie-details-hero">
    <div class="container">
        <!-- Back Navigation -->
        <div>
            <a href="{{ url()->previous() != url()->current() ? url()->previous() : route('movies.index') }}" class="back-nav-link">
                <i class="bi bi-arrow-left"></i> Back to catalog
            </a>
        </div>

        <div class="row g-4 g-lg-5">
            <!-- Left Column: Movie Poster & Quick Stats (Stacked on mobile, 4-cols on desktop) -->
            <div class="col-12 col-md-5 col-lg-4 movie-poster-column">
                <div class="details-poster-wrap">
                    @if($movie->poster_path)
                        @php
                            $posterSrc = str_starts_with($movie->poster_path, 'http')
                                ? $movie->poster_path
                                : asset('storage/' . $movie->poster_path);
                        @endphp
                        <img
                            src="{{ $posterSrc }}"
                            alt="{{ $movie->clean_title }}"
                            class="details-poster-img"
                            onerror="this.onerror=null;this.parentElement.innerHTML='<div class=\'details-poster-fallback\'><i class=\'bi bi-film\'></i><p class=\'fw-bold mb-0\'>{{ addslashes($movie->clean_title) }}</p></div>';"
                        >
                    @else
                        <div class="details-poster-fallback">
                            <i class="bi bi-film"></i>
                            <p class="fw-bold mb-0">{{ $movie->clean_title }}</p>
                            <span class="small text-muted">Poster not available</span>
                        </div>
                    @endif
                </div>

                <!-- Poster Meta Stats -->
                <div class="poster-meta-stats">
                    <div class="stat-item">
                        <div class="stat-value">{{ $movie->release_year ?? 'N/A' }}</div>
                        <div class="stat-label">Year</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ $movie->runtime ? $movie->runtime . 'm' : 'N/A' }}</div>
                        <div class="stat-label">Runtime</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ number_format($movie->view_count) }}</div>
                        <div class="stat-label">Views</div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Title, Metadata, Description, Qualities & Downloads -->
            <div class="col-12 col-md-7 col-lg-8 movie-info-column">
                <!-- Movie Title -->
                <h1 class="movie-main-title">{{ $movie->clean_title }}</h1>

                <!-- Year · Language · Genre Badges Bar -->
                <div class="movie-meta-bar">
                    <span class="badge-meta">
                        <i class="bi bi-calendar3 me-1"></i> {{ $movie->release_year ?? 'Not available' }}
                    </span>

                    @forelse($movie->languages as $lang)
                        <span class="badge-lang">
                            <i class="bi bi-translate me-1"></i> {{ $lang->name }}
                        </span>
                    @empty
                        <span class="badge-lang">
                            <i class="bi bi-translate me-1"></i> Not available
                        </span>
                    @endforelse

                    @forelse($movie->genres as $genre)
                        <span class="badge-genre">{{ $genre->name }}</span>
                    @empty
                        <!-- Fallback if no genre attached -->
                        <span class="badge-genre">Cinema</span>
                    @endforelse

                    @if($movie->runtime)
                        <span class="badge-meta">
                            <i class="bi bi-clock me-1"></i> {{ $movie->runtime }} mins
                        </span>
                    @endif
                </div>

                <!-- Description / Plot Summary -->
                <div class="synopsis-block">
                    <div class="synopsis-heading">
                        <i class="bi bi-card-text"></i> Plot Summary
                    </div>
                    <p class="synopsis-text">
                        {{ $movie->clean_description }}
                    </p>
                </div>

                <!-- Available Qualities & Authorized Download Flow -->
                <div class="qualities-section">
                    <div class="qualities-header">
                        <h3 class="qualities-title">
                            <i class="bi bi-cloud-arrow-down-fill"></i> Available Qualities
                        </h3>
                        @if($movie->links->isNotEmpty())
                            <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill small">
                                <i class="bi bi-shield-check me-1"></i> Verified Authorized Links
                            </span>
                        @endif
                    </div>

                    @if($movie->links->isNotEmpty())
                        @php
                            $defaultLink = $movie->links->first();
                        @endphp

                        <!-- Interactive Quality Option Selector Group -->
                        <div class="quality-selection-container">
                            <p class="small text-muted mb-2">Select quality to download:</p>
                            <div class="quality-selector-group" id="qualitySelector">
                                @foreach($movie->links as $index => $link)
                                    <button
                                        type="button"
                                        class="quality-option-btn {{ $index === 0 ? 'active' : '' }}"
                                        data-link-id="{{ $link->id }}"
                                        data-download-url="{{ route('downloads.redirect', $link) }}"
                                        data-quality="{{ $link->quality }}"
                                        data-resolution="{{ $link->resolution ?? 'Standard HD' }}"
                                        data-size="{{ $link->file_size ?? 'Optimized' }}"
                                        onclick="selectQuality(this)"
                                    >
                                        <span class="quality-title">
                                            {{ $link->quality }}
                                            <i class="bi bi-check-circle-fill text-primary ms-1" style="{{ $index === 0 ? '' : 'display:none;' }}"></i>
                                        </span>
                                        <span class="quality-info">
                                            {{ $link->file_size ?? 'Direct Link' }}
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Primary Download CTA Box -->
                        <div class="download-cta-box">
                            <div class="download-meta-preview">
                                <div>Selected: <span id="selectedQualityDisplay" class="preview-quality">{{ $defaultLink->quality }}</span></div>
                                <span>•</span>
                                <div id="selectedResolutionDisplay">{{ $defaultLink->resolution ?? 'Standard' }}</div>
                                <span>•</span>
                                <div id="selectedSizeDisplay">{{ $defaultLink->file_size ?? 'Unknown Size' }}</div>
                            </div>

                            <a
                                id="mainDownloadBtn"
                                href="{{ route('downloads.redirect', $defaultLink) }}"
                                target="_blank"
                                class="btn btn-download-primary"
                            >
                                <i class="bi bi-download"></i> Download Movie
                            </a>
                            <span class="text-dim small mt-2">Direct download link will open securely in a new tab.</span>
                        </div>

                        <!-- Direct Links Breakdown List -->
                        <div class="direct-links-list">
                            <div class="direct-links-heading">All Download Mirror Options</div>
                            @foreach($movie->links as $link)
                                <div class="link-row-item">
                                    <div class="link-left">
                                        <span class="quality-badge">{{ $link->quality }}</span>
                                        <div class="meta-specs">
                                            <span>Resolution: <strong>{{ $link->resolution ?? 'Standard' }}</strong></span>
                                            <span class="mx-2">•</span>
                                            <span>Size: <strong>{{ $link->file_size ?? 'N/A' }}</strong></span>
                                        </div>
                                    </div>
                                    <a href="{{ route('downloads.redirect', $link) }}" target="_blank" class="btn-download-sm">
                                        <i class="bi bi-download me-1"></i> Download
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert bg-dark border-secondary text-muted text-center py-4 mb-0">
                            <i class="bi bi-info-circle fs-3 d-block mb-2 text-warning"></i>
                            <h5 class="text-white">No authorized downloads available</h5>
                            <p class="small mb-0">This title is currently indexed in the catalog, but active download links are temporarily unavailable.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function selectQuality(btn) {
    // Update active button state
    document.querySelectorAll('#qualitySelector .quality-option-btn').forEach(b => {
        b.classList.remove('active');
        const check = b.querySelector('.bi-check-circle-fill');
        if (check) check.style.display = 'none';
    });

    btn.classList.add('active');
    const check = btn.querySelector('.bi-check-circle-fill');
    if (check) check.style.display = 'inline-block';

    // Update displays
    const quality = btn.dataset.quality;
    const resolution = btn.dataset.resolution;
    const size = btn.dataset.size;
    const downloadUrl = btn.dataset.downloadUrl;

    document.getElementById('selectedQualityDisplay').textContent = quality;
    document.getElementById('selectedResolutionDisplay').textContent = resolution;
    document.getElementById('selectedSizeDisplay').textContent = size;
    document.getElementById('mainDownloadBtn').href = downloadUrl;
}
</script>
@endsection
