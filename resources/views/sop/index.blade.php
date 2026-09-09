<x-knowledge-base.layout title="Daftar SOP — IT Knowledge Base">
    <main class="page">
        <div class="page-inner">
            <div class="breadcrumb">
                <a href="{{ route('knowledge-base') }}">Knowledge base</a>
                <x-icon name="chevron-right" :size="14" />
                <strong>SOP</strong>
            </div>

            <section class="intro-row">
                <div>
                    <h1>Standar Operasional Prosedur</h1>
                    <p class="intro-copy">Pembuatan dan pengelolaan dokumen SOP terstruktur (format SOP AP).</p>
                </div>
                @auth
                    <a href="{{ route('sop.create') }}" class="primary-button"><x-icon name="file-text" :size="16" /> Buat SOP</a>
                @endauth
            </section>

            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @forelse ($sops as $sop)
                <article class="thesis-card">
                    <div class="thesis-icon"><x-icon name="file-text" :size="20" /></div>
                    <div class="thesis-body">
                        <h3><a href="{{ route('sop.show', $sop) }}">{{ $sop->nama_sop }}</a></h3>
                        <div class="thesis-meta">
                            <span class="tag">{{ $sop->nomor_sop }}</span>
                            @if ($sop->tgl_efektif)<span>Efektif {{ $sop->tgl_efektif->format('d M Y') }}</span>@endif
                            <span class="sop-status sop-status-{{ $sop->status }}">{{ ucfirst($sop->status) }}</span>
                        </div>
                    </div>
                    <div class="thesis-actions">
                        <a class="secondary-button" href="{{ route('sop.show', $sop) }}"><x-icon name="book-open" :size="15" /> Lihat</a>
                        <a class="secondary-button" href="{{ route('sop.pdf', $sop) }}"><x-icon name="arrow-right" :size="15" /> PDF</a>
                    </div>
                </article>
            @empty
                <div class="empty-state">
                    <x-icon name="file-text" :size="24" />
                    <h3>Belum ada SOP</h3>
                    <p>@auth Klik "Buat SOP" untuk menambahkan. @else Masuk untuk membuat SOP. @endauth</p>
                </div>
            @endforelse
        </div>
    </main>
</x-knowledge-base.layout>
