<x-knowledge-base.layout title="Arsip Tugas Akhir — IT Knowledge Base">
    <main class="page">
        <div class="page-inner">
            <div class="breadcrumb">
                <a href="{{ route('knowledge-base') }}">Knowledge base</a>
                <x-icon name="chevron-right" :size="14" />
                <strong>Arsip Tugas Akhir</strong>
            </div>

            <section class="intro-row">
                <div>
                    <h1>Arsip Tugas Akhir</h1>
                    <p class="intro-copy">Telusuri arsip TA berdasarkan program studi dan tahun. Perguruan tinggi dapat berasal dari dalam maupun luar ITK.</p>
                </div>
                @auth
                    <button type="button" class="primary-button" data-thesis-form-toggle>
                        <x-icon name="file-text" :size="16" /> Tambah Arsip TA
                    </button>
                @endauth
            </section>

            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            {{-- ===== Form tambah arsip TA (hanya untuk user yang login) ===== --}}
            @auth
            <section class="thesis-form-wrap" data-thesis-form @if (! $errors->any()) hidden @endif>
                <form class="thesis-form" method="POST" action="{{ route('knowledge-base.archive.store') }}" enctype="multipart/form-data">
                    @csrf
                    <h2>Tambah Arsip Tugas Akhir</h2>
                    <p class="form-hint">Sertakan <strong>tautan Google Drive</strong> atau <strong>unggah berkas PDF</strong> (salah satu wajib diisi).</p>

                    <div class="form-grid">
                        <label class="field field-wide">
                            <span>Judul TA</span>
                            <input type="text" name="title" value="{{ old('title') }}" required>
                            @error('title') <em class="field-error">{{ $message }}</em> @enderror
                        </label>

                        <label class="field">
                            <span>Penulis (Author)</span>
                            <input type="text" name="author" value="{{ old('author') }}" required>
                            @error('author') <em class="field-error">{{ $message }}</em> @enderror
                        </label>

                        <label class="field">
                            <span>Perguruan Tinggi (Asal)</span>
                            <input type="text" name="university" value="{{ old('university') }}" required>
                            @error('university') <em class="field-error">{{ $message }}</em> @enderror
                        </label>

                        <label class="field">
                            <span>Program Studi</span>
                            <input type="text" name="study_program" value="{{ old('study_program') }}" required>
                            @error('study_program') <em class="field-error">{{ $message }}</em> @enderror
                        </label>

                        <label class="field">
                            <span>Tahun Penelitian</span>
                            <input type="number" name="year" value="{{ old('year', date('Y')) }}" min="1980" max="{{ date('Y') + 1 }}" required>
                            @error('year') <em class="field-error">{{ $message }}</em> @enderror
                        </label>

                        <label class="field field-wide">
                            <span>Tautan Google Drive <small>(opsional jika mengunggah PDF)</small></span>
                            <input type="url" name="drive_url" value="{{ old('drive_url') }}" placeholder="https://drive.google.com/...">
                            @error('drive_url') <em class="field-error">{{ $message }}</em> @enderror
                        </label>

                        <label class="field field-wide">
                            <span>Unggah Berkas PDF <small>(opsional jika mengisi tautan, maks. 20 MB)</small></span>
                            <input type="file" name="document" accept="application/pdf">
                            @error('document') <em class="field-error">{{ $message }}</em> @enderror
                        </label>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="primary-button">Simpan Arsip <x-icon name="arrow-right" :size="16" /></button>
                        <button type="button" class="secondary-button" data-thesis-form-toggle>Batal</button>
                    </div>
                </form>
            </section>
            @endauth

            {{-- ===== Filter ===== --}}
            <form class="filter-bar" method="GET" action="{{ route('knowledge-base.archive') }}">
                <div class="search-box">
                    <x-icon name="search" :size="20" />
                    <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Cari judul, penulis, perguruan tinggi, atau prodi..." aria-label="Cari arsip TA">
                </div>
                <select name="study_program" aria-label="Filter program studi">
                    <option value="">Semua Program Studi</option>
                    @foreach ($programs as $program)
                        <option value="{{ $program }}" @selected($filters['study_program'] === $program)>{{ $program }}</option>
                    @endforeach
                </select>
                <select name="year" aria-label="Filter tahun">
                    <option value="">Semua Tahun</option>
                    @foreach ($years as $year)
                        <option value="{{ $year }}" @selected((int) $filters['year'] === (int) $year)>{{ $year }}</option>
                    @endforeach
                </select>
                <button type="submit" class="secondary-button">Terapkan</button>
                @if ($filters['search'] || $filters['year'] || $filters['study_program'])
                    <a class="text-button" href="{{ route('knowledge-base.archive') }}">Reset</a>
                @endif
            </form>

            {{-- ===== Daftar arsip ===== --}}
            <p class="result-count">{{ $theses->count() }} arsip ditemukan</p>

            @if ($theses->isNotEmpty())
                <div class="table-wrap">
                    <table class="thesis-table">
                        <thead>
                            <tr>
                                <th class="col-no">#</th>
                                <th>Judul TA</th>
                                <th>Penulis</th>
                                <th>Perguruan Tinggi</th>
                                <th>Program Studi</th>
                                <th class="col-year">Tahun</th>
                                <th class="col-actions">Berkas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($theses as $thesis)
                                <tr>
                                    <td class="col-no">{{ $loop->iteration }}</td>
                                    <td data-label="Judul TA" class="cell-title">{{ $thesis->title }}</td>
                                    <td data-label="Penulis">{{ $thesis->author }}</td>
                                    <td data-label="Perguruan Tinggi">{{ $thesis->university }}</td>
                                    <td data-label="Program Studi"><span class="tag">{{ $thesis->study_program }}</span></td>
                                    <td data-label="Tahun" class="col-year">{{ $thesis->year }}</td>
                                    <td data-label="Berkas" class="col-actions">
                                        @if ($thesis->hasUploadedFile())
                                            <a class="table-link" href="{{ $thesis->fileUrl() }}" target="_blank" rel="noopener"><x-icon name="book-open" :size="14" /> Lihat</a>
                                            <a class="table-link" href="{{ $thesis->fileUrl() }}" download><x-icon name="arrow-right" :size="14" /> Download</a>
                                        @elseif ($thesis->drive_url)
                                            <a class="table-link" href="{{ $thesis->drive_url }}" target="_blank" rel="noopener"><x-icon name="arrow-right" :size="14" /> Buka tautan</a>
                                        @else
                                            <span class="thesis-nolink">Belum tersedia</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <x-icon name="search" :size="24" />
                    <h3>Arsip tidak ditemukan</h3>
                    <p>Coba ubah kata kunci atau filter, atau tambahkan arsip baru.</p>
                </div>
            @endif
        </div>
    </main>
</x-knowledge-base.layout>
