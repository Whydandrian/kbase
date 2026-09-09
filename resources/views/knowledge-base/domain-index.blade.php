<x-knowledge-base.layout :title="$pageTitle . ' — IT Knowledge Base'">
    <main class="page">
        <div class="page-inner">
            <div class="breadcrumb">
                <a href="{{ route('knowledge-base') }}">Knowledge base</a>
                <x-icon name="chevron-right" :size="14" />
                <strong>{{ $pageTitle }}</strong>
            </div>

            <section class="intro-row">
                <div>
                    <h1>{{ $pageTitle }}</h1>
                    <p class="intro-copy">{{ $pageSubtitle }}</p>
                </div>
            </section>

            <div class="landing-team-grid page-grid">
                @foreach ($domains as $domain)
                    <a class="landing-team-card accent-{{ $domain->accent }}" href="{{ route($routeName, $domain) }}">
                        <div class="card-icon"><x-icon :name="$domain->icon" :size="22" /></div>
                        <h3>{{ $domain->name }}</h3>
                        <p>{{ $domain->description }}</p>
                        <span class="landing-team-link">{{ $domain->categories_count }} kategori · {{ $domain->documents_count }} dokumen <x-icon name="arrow-right" :size="15" /></span>
                    </a>
                @endforeach
            </div>
        </div>
    </main>
</x-knowledge-base.layout>
