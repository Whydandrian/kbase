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
    {{-- ===== Impersonate banner ===== --}}
    @if ($isImpersonating ?? false)
        <div class="impersonate-bar">
            <x-icon name="shield-check" :size="15" />
            <span>Anda sedang melihat sebagai <strong>{{ auth()->user()->name }}</strong></span>
            <form method="POST" action="{{ route('impersonate.stop') }}" style="margin:0;">
                @csrf
                <button type="submit" class="impersonate-stop-btn">Berhenti &amp; Kembali ke Admin</button>
            </form>
        </div>
    @endif

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
                    @if (auth()->user()->isAdmin())
                        {{-- Admin: dropdown "Kelola Data" menggabungkan SOP + Pengguna --}}
                        <div class="nav-dropdown" @class(['is-active' => request()->routeIs('sop.*', 'admin.*')])>
                            <button type="button" class="nav-dropdown-trigger" aria-haspopup="true" aria-expanded="false" data-dropdown-toggle>
                                Kelola Data <x-icon name="chevron-down" :size="13" />
                            </button>
                            <div class="nav-dropdown-menu" role="menu">
                                <a href="{{ route('sop.index') }}" role="menuitem" @class(['is-active' => request()->routeIs('sop.*')])>
                                    <x-icon name="file-text" :size="15" /> SOP
                                </a>
                                <a href="{{ route('admin.users.index') }}" role="menuitem" @class(['is-active' => request()->routeIs('admin.*')])>
                                    <x-icon name="users" :size="15" /> Pengguna
                                </a>
                            </div>
                        </div>
                    @else
                        {{-- User login biasa: hanya SOP --}}
                        <a href="{{ route('sop.index') }}" @class(['is-active' => request()->routeIs('sop.*')])>SOP</a>
                    @endif
                @endauth
                <button type="button" class="nav-search-trigger" data-search-open aria-label="Cari (Ctrl/Cmd + K)">
                    <x-icon name="search" :size="15" /><span>Cari</span><kbd data-search-hint>⌘ K</kbd>
                </button>
            </nav>

            {{-- Auth actions: always visible, outside nav-links so never hidden on mobile --}}
            <div class="nav-end">
                @auth
                    <div class="nav-dropdown nav-profile-dropdown" @class(['is-active' => request()->routeIs('profile')])>
                        <button type="button" class="nav-profile-trigger" data-dropdown-toggle aria-haspopup="true" aria-expanded="false">
                            <span class="nav-profile-avatar">{{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}</span>
                            <span class="nav-profile-name">{{ auth()->user()->name }}</span>
                            <x-icon name="chevron-down" :size="13" />
                        </button>
                        <div class="nav-dropdown-menu nav-profile-menu" role="menu">
                            <div class="nav-profile-info">
                                <strong>{{ auth()->user()->name }}</strong>
                                <span>{{ auth()->user()->email }}</span>
                                @if (auth()->user()->isAdmin())
                                    <span class="badge-role admin" style="margin-top:4px;">Administrator</span>
                                @endif
                            </div>
                            <div class="nav-profile-divider"></div>
                            <a href="{{ route('profile.show') }}" role="menuitem" @class(['is-active' => request()->routeIs('profile')])>
                                <x-icon name="settings" :size="15" /> Profil Saya
                            </a>
                            @if ($isImpersonating ?? false)
                                <div class="nav-profile-divider"></div>
                                <form method="POST" action="{{ route('impersonate.stop') }}">
                                    @csrf
                                    <button type="submit" class="nav-profile-stop-btn">
                                        <x-icon name="arrow-right" :size="15" class="rotate-180" /> Berhenti Impersonate
                                    </button>
                                </form>
                            @endif
                            <div class="nav-profile-divider"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="nav-profile-logout">
                                    <x-icon name="x" :size="15" /> Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="landing-nav-cta">
                        <x-icon name="shield-check" :size="14" /> Masuk
                    </a>
                @endauth
            </div>
        </div>
    </header>

    {{ $slot }}

    {{-- ===== Kontak ===== --}}
    <section class="contact-section">
        <div class="contact-inner">
            <div class="contact-text">
                <h2>Butuh bantuan?</h2>
                <p>Hubungi tim UPT TIK Institut Teknologi Kalimantan melalui WhatsApp untuk pertanyaan, permintaan akses, atau laporan kendala teknis.</p>
            </div>
            <a class="contact-wa-btn"
               href="https://wa.me/6281234567890"
               target="_blank"
               rel="noopener noreferrer"
               aria-label="Hubungi via WhatsApp">
                {{-- WhatsApp icon inline --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/>
                </svg>
                Hubungi via WhatsApp
            </a>
        </div>
    </section>

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
