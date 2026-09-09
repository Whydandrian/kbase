<x-knowledge-base.layout title="Manajemen Pengguna — IT Knowledge Base">
    <main class="page">
        <div class="page-inner">
            <div class="breadcrumb">
                <a href="{{ route('knowledge-base') }}">Knowledge base</a>
                <x-icon name="chevron-right" :size="14" />
                <strong>Manajemen Pengguna</strong>
            </div>

            <section class="intro-row">
                <div>
                    <h1>Manajemen Pengguna</h1>
                    <p class="intro-copy">Tambah atau hapus akun petugas yang dapat mengakses fitur pengelolaan dokumen dan SOP.</p>
                </div>
                <button type="button" class="primary-button" data-user-form-toggle>
                    <x-icon name="users" :size="16" /> Tambah Pengguna
                </button>
            </section>

            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            {{-- Form tambah pengguna --}}
            <section class="thesis-form-wrap" data-user-form @if (! $errors->any()) hidden @endif>
                <form class="thesis-form" method="POST" action="{{ route('admin.users.store') }}">
                    @csrf
                    <h2>Tambah Pengguna Baru</h2>
                    <div class="form-grid">
                        <label class="field">
                            <span>Nama</span>
                            <input type="text" name="name" value="{{ old('name') }}" required autofocus>
                            @error('name') <em class="field-error">{{ $message }}</em> @enderror
                        </label>
                        <label class="field">
                            <span>Email</span>
                            <input type="email" name="email" value="{{ old('email') }}" required>
                            @error('email') <em class="field-error">{{ $message }}</em> @enderror
                        </label>
                        <label class="field">
                            <span>Jabatan</span>
                            <input type="text" name="jabatan" value="{{ old('jabatan') }}" placeholder="Misal: Pengadministrasi Umum">
                            @error('jabatan') <em class="field-error">{{ $message }}</em> @enderror
                        </label>
                        <label class="field">
                            <span>Kata Sandi</span>
                            <input type="password" name="password" required autocomplete="new-password">
                            @error('password') <em class="field-error">{{ $message }}</em> @enderror
                        </label>
                        <label class="field">
                            <span>Konfirmasi Kata Sandi</span>
                            <input type="password" name="password_confirmation" required autocomplete="new-password">
                        </label>
                        <label class="field-check" style="align-self:end;padding-bottom:10px;">
                            <input type="checkbox" name="is_admin" value="1" @checked(old('is_admin'))>
                            <span>Tandai sebagai Administrator</span>
                        </label>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="primary-button">Simpan Pengguna <x-icon name="arrow-right" :size="16" /></button>
                        <button type="button" class="secondary-button" data-user-form-toggle>Batal</button>
                    </div>
                </form>
            </section>

            {{-- Daftar pengguna --}}
            <div class="table-wrap">
                <table class="thesis-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Jabatan</th>
                            <th>Peran</th>
                            <th>TTD</th>
                            <th class="col-actions">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td class="col-no">{{ $loop->iteration }}</td>
                                <td data-label="Nama" class="cell-title">{{ $user->name }}</td>
                                <td data-label="Email">{{ $user->email }}</td>
                                <td data-label="Jabatan">{{ $user->jabatan ?? '—' }}</td>
                                <td data-label="Peran">
                                    @if ($user->isAdmin())
                                        <span class="badge-role admin">Administrator</span>
                                    @else
                                        <span class="badge-role">Petugas</span>
                                    @endif
                                </td>
                                <td data-label="TTD">
                                    @if ($user->hasSignature())
                                        <span class="badge-role admin">Ada</span>
                                    @else
                                        <span class="sop-unsigned">Belum</span>
                                    @endif
                                </td>
                                <td data-label="Aksi" class="col-actions">
                                    @if ($user->id !== auth()->id())
                                        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                                            {{-- Impersonate --}}
                                            @if (! ($isImpersonating ?? false))
                                                <form method="POST" action="{{ route('impersonate.start', $user) }}">
                                                    @csrf
                                                    <button type="submit" class="table-link" title="Masuk sebagai pengguna ini">
                                                        <x-icon name="users" :size="14" /> Impersonate
                                                    </button>
                                                </form>
                                            @endif
                                            {{-- Hapus --}}
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                                onsubmit="return confirm('Hapus pengguna {{ $user->name }}?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="table-link" style="color:var(--danger-700);">
                                                    <x-icon name="x" :size="14" /> Hapus
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="sop-unsigned">(akun Anda)</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</x-knowledge-base.layout>
