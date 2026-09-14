<a href="{{ route('movies.show', $movie) }}" class="movie-card">
    <!-- Poster Section -->
    <div class="poster-wrap">
        @if($movie->poster_path)
            @php
                $posterSrc = str_starts_with($movie->poster_path, 'http')
                    ? $movie->poster_path
                    : asset('storage/' . $movie->poster_path);
            @endphp
            <img src="{{ $posterSrc }}" alt="{{ $movie->clean_title }}" class="poster-img" loading="lazy" onerror="this.onerror=null;this.parentElement.innerHTML='<div class=\'poster-fallback\'><i class=\'bi bi-film\'></i><span>{{ addslashes($movie->clean_title) }}</span></div>';">
        @else
            <div class="poster-fallback">
                <i class="bi bi-film"></i>
                <span>{{ $movie->clean_title }}</span>
            </div>
        @endif

        <div class="poster-gradient-bottom"></div>

        @if($movie->runtime)
            <span class="poster-top-badge">{{ $movie->runtime }}m</span>
        @endif
    </div>

    <!-- Details Section -->
    <div class="card-body">
        <div>
            <h5 class="movie-title" title="{{ $movie->clean_title }}">{{ $movie->clean_title }}</h5>
            <div class="movie-meta">
                <span>{{ $movie->release_year ?? 'N/A' }}</span>
                <span class="meta-dot">·</span>
                <span>
                    @if($movie->languages->isNotEmpty())
                        {{ $movie->languages->pluck('name')->implode(', ') }}
                    @else
                        Tamil
                    @endif
                </span>
            </div>
        </div>

        <!-- Qualities Row: 1080p · 720p · 480p -->
        <div class="movie-qualities">
            @if($movie->links->isNotEmpty())
                @foreach($movie->links->take(3) as $link)
                    @php
                        $qLower = strtolower($link->quality);
                        $qClass = match(true) {
                            str_contains($qLower, '1080') => 'quality-1080p',
                            str_contains($qLower, '720')  => 'quality-720p',
                            str_contains($qLower, '480')  => 'quality-480p',
                            str_contains($qLower, '360')  => 'quality-360p',
                            default => ''
                        };
                    @endphp
                    <span class="quality-pill {{ $qClass }}">{{ $link->quality }}</span>
                @endforeach
            @else
                <span class="no-qualities">HD Available</span>
            @endif
        </div>
    </div>
</a>
