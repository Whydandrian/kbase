@php($isAdmin = auth()->user()?->isAdmin() ?? false)
<x-knowledge-base.layout :title="$domain->name . ' — IT Knowledge Base'">
    <div class="workspace">
        <aside class="sidebar" data-sidebar>
            <div class="sidebar-inner">
                <div class="sidebar-header">
                    <span>Kategori</span>
                    <button class="icon-button close-nav" data-nav-close aria-label="Tutup navigasi"><x-icon name="x" :size="18" /></button>
                </div>
                <nav class="primary-nav">
                    <button class="nav-item active" data-filter="all">
                        <x-icon name="book-open" :size="17" /><span>Semua</span><span class="nav-count">{{ $documentCount }}</span>
                    </button>
                    @foreach ($domain->categories as $category)
                        <button class="nav-item" data-filter="{{ $category->slug }}">
                            <x-icon :name="$category->icon" :size="17" /><span>{{ $category->name }}</span><span class="nav-count">{{ $category->documents->count() }}</span>
                        </button>
                    @endforeach
                </nav>
            </div>
        </aside>
        <button class="sidebar-overlay" data-nav-close aria-label="Tutup overlay navigasi" hidden></button>

        <main class="main-content">
            <button class="mobile-menu-button listing-menu" data-nav-open aria-label="Buka navigasi"><x-icon name="menu" :size="20" /> Kategori</button>

            <div class="breadcrumb">
                <a href="{{ route('knowledge-base') }}">Knowledge base</a>
                <x-icon name="chevron-right" :size="14" />
                <a href="{{ route($backRoute) }}">{{ $domain->type === 'team' ? 'Tim' : 'Domain' }}</a>
                <x-icon name="chevron-right" :size="14" />
                <strong>{{ $domain->name }}</strong>
            </div>

            <section class="intro-row">
                <div>
                    <h1>{{ $domain->name }}</h1>
                    <p class="intro-copy">{{ $domain->description }}</p>
                </div>
            </section>

            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <div class="search-box">
                <x-icon name="search" :size="20" />
                <input data-search placeholder="Cari dokumen dalam {{ $domain->name }}..." aria-label="Cari dokumen">
            </div>

            @forelse ($domain->categories as $category)
                <section class="section-block cat-color-{{ $loop->index % 6 }}" data-category-block id="{{ $category->slug }}">
                    <div class="section-heading">
                        <div>
                            <h2>{{ $category->name }}</h2>
                            <p>{{ $category->description }}</p>
                        </div>
                        <div class="section-heading-actions">
                            <span class="tag tag-category">{{ $category->documents->count() }} dokumen</span>
                            @if ($isAdmin)
                                <button type="button" class="primary-button"
                                    data-doc-add
                                    data-action="{{ route('documents.store', $category) }}">
                                    <x-icon name="file-text" :size="15" /> Tambah dokumen
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="document-list">
                        @forelse ($category->documents as $document)
                            <div class="document-row @unless($document->is_active) is-inactive @endunless"
                                data-document
                                data-category="{{ $category->slug }}"
                                data-haystack="{{ Str::lower($document->title.' '.$document->description.' '.$document->doc_category.' '.$document->owner) }}"
                                data-title="{{ $document->title }}"
                                data-description="{{ $document->description }}"
                                data-doc-category="{{ $document->doc_category }}"
                                data-owner="{{ $document->owner }}"
                                data-mode="{{ $document->sourceMode() }}"
                                data-url="{{ $document->url }}"
                                data-file="{{ $document->hasUploadedFile() ? route('documents.file', $document) : '' }}"
                                data-icon="{{ $document->icon }}">
                                <button type="button" class="document-open" data-doc-open>
                                    <div class="document-icon"><x-icon :name="$document->icon" :size="18" /></div>
                                    <div class="document-main">
                                        <h3>{{ $document->title }}
                                            @unless($document->is_active)<span class="badge-inactive">Nonaktif</span>@endunless
                                        </h3>
                                        <p>{{ $document->description }}</p>
                                        <div class="document-meta">
                                            @if ($document->doc_category)<span class="tag">{{ $document->doc_category }}</span>@endif
                                            @if ($document->owner)<span>{{ $document->owner }}</span>@endif
                                            <span class="doc-source">{{ $document->sourceMode() === 'pdf' ? 'PDF' : ($document->sourceMode() === 'url' ? 'URL' : '—') }}</span>
                                        </div>
                                    </div>
                                </button>
                                @if ($isAdmin)
                                    <div class="document-admin">
                                        <button type="button" class="icon-button" title="Edit"
                                            data-doc-edit
                                            data-action="{{ route('documents.update', $document) }}"
                                            data-title="{{ $document->title }}"
                                            data-description="{{ $document->description }}"
                                            data-doc-category="{{ $document->doc_category }}"
                                            data-owner="{{ $document->owner }}"
                                            data-url="{{ $document->url }}"
                                            data-active="{{ $document->is_active ? 1 : 0 }}"
                                            data-has-file="{{ $document->hasUploadedFile() ? 1 : 0 }}">
                                            <x-icon name="settings" :size="16" />
                                        </button>
                                        <form method="POST" action="{{ route('documents.toggle', $document) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="icon-button" title="{{ $document->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                <x-icon name="{{ $document->is_active ? 'shield-check' : 'clock' }}" :size="16" />
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('documents.destroy', $document) }}" onsubmit="return confirm('Hapus dokumen ini?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="icon-button icon-danger" title="Hapus"><x-icon name="x" :size="16" /></button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="document-row-empty">Belum ada dokumen pada kategori ini.</div>
                        @endforelse
                    </div>
                </section>
            @empty
                <div class="empty-state">
                    <x-icon name="folder" :size="24" />
                    <h3>Belum ada kategori</h3>
                    <p>Domain ini belum memiliki kategori dokumen.</p>
                </div>
            @endforelse

            <div class="empty-state" data-empty-state hidden>
                <x-icon name="search" :size="24" />
                <h3>Dokumen tidak ditemukan</h3>
                <p>Coba kata kunci lain atau pilih kategori berbeda.</p>
            </div>
        </main>
    </div>

    {{-- ===================== DOCUMENT MODAL (view) ===================== --}}
    <div class="modal-backdrop" data-modal hidden>
        <article class="document-modal document-modal-wide" data-modal-card>
            <button class="modal-close icon-button" data-modal-close aria-label="Tutup dokumen"><x-icon name="x" :size="18" /></button>
            <div class="document-icon large" data-modal-icon></div>
            <span class="tag" data-modal-category></span>
            <h2 data-modal-title></h2>
            <p data-modal-description></p>
            <div class="modal-note" data-modal-owner-wrap>Dikelola oleh <strong data-modal-owner></strong>.</div>

            {{-- PDF preview --}}
            <div class="pdf-preview" data-modal-pdf hidden>
                <iframe title="Pratinjau PDF" data-modal-iframe></iframe>
                <a class="secondary-button" data-modal-download target="_blank" rel="noopener"><x-icon name="arrow-right" :size="15" /> Unduh PDF</a>
            </div>

            {{-- URL open --}}
            <a class="primary-button" data-modal-url href="#" target="_blank" rel="noopener" hidden>Buka di tab baru <x-icon name="arrow-right" :size="16" /></a>
        </article>
    </div>

    @if ($isAdmin)
        {{-- ===================== DOCUMENT FORM MODAL (add/edit) ===================== --}}
        <div class="modal-backdrop" data-doc-form-modal data-has-errors="{{ $errors->any() ? '1' : '0' }}" hidden>
            <form class="document-modal document-form" method="POST" enctype="multipart/form-data" data-doc-form data-modal-card>
                @csrf
                <input type="hidden" name="_method" value="POST" data-form-method>
                <button type="button" class="modal-close icon-button" data-doc-form-close aria-label="Tutup"><x-icon name="x" :size="18" /></button>
                <h2 data-form-heading>Tambah Dokumen</h2>
                <p class="form-hint">Isi <strong>tautan URL</strong> (dibuka di tab baru) atau unggah <strong>berkas PDF</strong> (ditampilkan sebagai pratinjau).</p>

                @if ($errors->any())
                    <div class="alert alert-error">
                        <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <label class="field">
                    <span>Judul Dokumen</span>
                    <input type="text" name="title" data-form-title required>
                </label>
                <label class="field">
                    <span>Deskripsi</span>
                    <input type="text" name="description" data-form-description>
                </label>
                <div class="form-grid">
                    <label class="field">
                        <span>Label (mis. SOP)</span>
                        <input type="text" name="doc_category" data-form-doc-category>
                    </label>
                    <label class="field">
                        <span>Pemilik</span>
                        <input type="text" name="owner" data-form-owner>
                    </label>
                </div>
                <label class="field">
                    <span>Tautan URL <small>(opsional jika unggah PDF)</small></span>
                    <input type="url" name="url" data-form-url placeholder="https://...">
                </label>
                <label class="field">
                    <span>Berkas PDF <small>(opsional jika mengisi URL, maks. 20 MB)</small></span>
                    <input type="file" name="document" accept="application/pdf">
                    <em class="field-note" data-form-file-note hidden>Sudah ada berkas terunggah. Unggah baru untuk mengganti.</em>
                </label>
                <label class="field-check">
                    <input type="checkbox" name="is_active" value="1" data-form-active checked> <span>Aktif (tampilkan ke publik)</span>
                </label>

                <div class="form-actions">
                    <button type="submit" class="primary-button">Simpan <x-icon name="arrow-right" :size="16" /></button>
                    <button type="button" class="secondary-button" data-doc-form-close>Batal</button>
                </div>
            </form>
        </div>
    @endif

    <div id="icon-templates" hidden>
        @foreach (['network','terminal','shield-check','file-text','book-open','settings','folder','layers','trending-up'] as $iconName)
            <template data-icon-template="{{ $iconName }}"><x-icon :name="$iconName" :size="22" /></template>
        @endforeach
    </div>
</x-knowledge-base.layout>
