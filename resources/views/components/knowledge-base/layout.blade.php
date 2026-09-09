@props(['title' => 'IT Knowledge Base'])
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="data:,">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'IT Knowledge Base' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div id="app">
    <header class="landing-nav">
        <div class="landing-nav-inner">
            <a class="brand-mark" href="{{ route('knowledge-base') }}">
                <div class="w-8 h-8">
                    <img src="{{ asset('lambang_itk_utama.webp') }}" alt="Logo ITK">
                </div>
                <div><strong>IT Knowledge Base</strong><span>Internal documentation portal</span></div>
            </a>
            <nav class="landing-nav-links">
                <a href="{{ route('knowledge-base') }}" @class(['is-active' => request()->routeIs('knowledge-base')])>Beranda</a>
                <a href="{{ route('knowledge-base.domains') }}" @class(['is-active' => request()->routeIs('knowledge-base.domains', 'knowledge-base.domain')])>Domain</a>
                <a href="{{ route('knowledge-base.teams') }}" @class(['is-active' => request()->routeIs('knowledge-base.teams', 'knowledge-base.team')])>Tim</a>
                <a href="{{ route('knowledge-base.archive') }}" @class(['is-active' => request()->routeIs('knowledge-base.archive')])>Arsip TA</a>
                @auth
                    <a href="{{ route('sop.index') }}" @class(['is-active' => request()->routeIs('sop.*')])>SOP</a>
                @endauth
                <button type="button" class="nav-search-trigger" data-search-open aria-label="Cari (Ctrl/Cmd + K)">
                    <x-icon name="search" :size="15" /><span>Cari</span><kbd data-search-hint>⌘ K</kbd>
                </button>
                @auth
                    <span class="nav-user" title="{{ auth()->user()->email }}">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="nav-logout">
                        @csrf
                        <button type="submit" class="nav-logout-btn">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" @class(['landing-nav-cta', 'is-active' => request()->routeIs('login')])>Masuk</a>
                @endauth
            </nav>
        </div>
    </header>

    {{ $slot }}

    <footer class="landing-footer">
        <div class="landing-footer-inner">
            <div class="brand-mark">
                <div class="brand-symbol"><x-icon name="book-open" :size="19" /></div>
                <div><strong>IT Knowledge Base</strong><span>Internal documentation portal</span></div>
            </div>
            <div class="footer-links">
                <a href="{{ route('knowledge-base.domains') }}">Domain</a>
                <a href="{{ route('knowledge-base.teams') }}">Tim</a>
                <a href="{{ route('knowledge-base.archive') }}">Arsip TA</a>
            </div>
            <p class="footer-copy">Internal use only · UPT TIK · Last reviewed September 2026</p>
        </div>
    </footer>

    {{-- ===================== GLOBAL SEARCH (Ctrl / Cmd + K) ===================== --}}
    <div class="search-backdrop" data-search-modal hidden>
        <div class="search-panel" data-search-panel role="dialog" aria-modal="true" aria-label="Pencarian global">
            <div class="search-panel-input">
                <x-icon name="search" :size="18" />
                <input type="text" data-search-input placeholder="Cari dokumen, kategori, domain, atau arsip TA..." aria-label="Kata kunci pencarian" autocomplete="off">
                <kbd>ESC</kbd>
            </div>
            <div class="search-panel-results" data-search-results>
                <p class="search-panel-hint">Ketik untuk mulai mencari.</p>
            </div>
        </div>
    </div>

    {{-- Search index consumed by the global search (built from server-rendered data). --}}
    <script type="application/json" data-search-index>@json($searchIndex ?? [])</script>
</div>
</body>
</html>
