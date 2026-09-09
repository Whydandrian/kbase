<x-knowledge-base.layout title="IT Knowledge Base">
    <section class="landing-hero">
        <div class="landing-hero-inner">
            <p class="eyebrow">Internal IT documentation</p>
            <h1 class="landing-headline">Semua yang tim Anda butuhkan,<br class="hide-mobile">dalam satu tempat yang tertata.</h1>
            <p class="landing-subhead">Dokumen manajerial tata kelola TI dan dokumentasi teknis tiap tim — tersimpan rapi, mudah dicari, dan cepat ditemukan.</p>
            <div class="landing-cta-row">
                <a class="primary-button large" href="{{ route('knowledge-base.domains') }}">Dokumen manajerial <x-icon name="arrow-right" :size="18" /></a>
                <a class="secondary-button large" href="{{ route('knowledge-base.teams') }}">Dokumen teknis per tim</a>
            </div>
            <button type="button" class="landing-search-peek" data-search-open>
                <x-icon name="search" :size="20" />
                <span>Cari dokumen, kategori, atau arsip TA...</span>
                <kbd data-search-hint>⌘ K</kbd>
            </button>
        </div>
    </section>

    {{-- ===== Dokumen manajerial (domain) ===== --}}
    <section class="landing-teams">
        <div class="landing-section-head">
            <p class="eyebrow">Dokumen manajerial</p>
            <h2>Telusuri berdasarkan domain tata kelola</h2>
            <p>Setiap domain memuat beberapa kategori dokumen yang dikelola UPT TIK.</p>
        </div>
        <div class="landing-team-grid">
            @foreach ($domains as $domain)
                <a class="landing-team-card accent-{{ $domain->accent }}" href="{{ route('knowledge-base.domain', $domain) }}">
                    <div class="card-icon"><x-icon :name="$domain->icon" :size="22" /></div>
                    <h3>{{ $domain->name }}</h3>
                    <p>{{ $domain->categories_count }} kategori · {{ $domain->documents_count }} dokumen</p>
                    <span class="landing-team-link">Buka <x-icon name="arrow-right" :size="15" /></span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- ===== Dokumen teknis (tim) ===== --}}
    <section class="landing-teams">
        <div class="landing-section-head">
            <p class="eyebrow">Dokumen teknis</p>
            <h2>Telusuri berdasarkan tim</h2>
            <p>Panduan teknis dan operasional yang dikelola masing-masing tim.</p>
        </div>
        <div class="landing-team-grid">
            @foreach ($teams as $domain)
                <a class="landing-team-card accent-{{ $domain->accent }}" href="{{ route('knowledge-base.team', $domain) }}">
                    <div class="card-icon"><x-icon :name="$domain->icon" :size="22" /></div>
                    <h3>{{ $domain->name }}</h3>
                    <p>{{ $domain->categories_count }} kategori · {{ $domain->documents_count }} dokumen</p>
                    <span class="landing-team-link">Buka <x-icon name="arrow-right" :size="15" /></span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- ===== Arsip TA ===== --}}
    @if ($archive)
        <section class="landing-cta-banner">
            <div class="cta-banner-inner">
                <div>
                    <h2>Arsip Tugas Akhir</h2>
                    <p>{{ $thesisCount }} arsip Tugas Akhir tersedia. Telusuri berdasarkan program studi dan tahun, atau tambahkan arsip baru.</p>
                </div>
                <a class="primary-button large" href="{{ route('knowledge-base.archive') }}">Buka Arsip TA <x-icon name="arrow-right" :size="18" /></a>
            </div>
        </section>
    @endif
</x-knowledge-base.layout>
