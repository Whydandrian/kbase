<x-knowledge-base.layout title="Profil Saya — IT Knowledge Base">
    <main class="page">
        <div class="page-inner" style="max-width:680px;">
            <div class="breadcrumb">
                <a href="{{ route('knowledge-base') }}">Knowledge base</a>
                <x-icon name="chevron-right" :size="14" />
                <strong>Profil Saya</strong>
            </div>

            <h1>Profil Saya</h1>

            {{-- ===== Data diri ===== --}}
            <div class="profile-card">
                <div class="profile-card-head">
                    <div class="profile-avatar-lg">{{ Str::upper(Str::substr($user->name, 0, 1)) }}</div>
                    <div>
                        <strong>{{ $user->name }}</strong>
                        <span>{{ $user->email }}</span>
                        @if ($user->jabatan)<span>{{ $user->jabatan }}</span>@endif
                        <span class="badge-role @if ($user->isAdmin()) admin @endif" style="margin-top:4px;">
                            {{ $user->isAdmin() ? 'Administrator' : 'Petugas' }}
                        </span>
                    </div>
                </div>
            </div>

            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="profile-form">
                @csrf @method('PUT')
                <h2>Data Diri</h2>

                <label class="field">
                    <span>Nama</span>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                    @error('name') <em class="field-error">{{ $message }}</em> @enderror
                </label>

                <label class="field">
                    <span>Email</span>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                    @error('email') <em class="field-error">{{ $message }}</em> @enderror
                </label>

                <label class="field">
                    <span>Jabatan</span>
                    <input type="text" name="jabatan" value="{{ old('jabatan', $user->jabatan) }}" placeholder="Misal: Pengadministrasi Umum">
                    @error('jabatan') <em class="field-error">{{ $message }}</em> @enderror
                </label>

                {{-- Signature / TTD --}}
                <div class="field">
                    <span>Tanda Tangan</span>
                    @if ($user->hasSignature())
                        <div class="profile-signature-wrap">
                            <img src="{{ route('profile.signature') }}" alt="Tanda tangan saya" class="profile-signature-img">
                            <div class="profile-signature-actions">
                                <label class="secondary-button" style="cursor:pointer;">
                                    <x-icon name="settings" :size="14" /> Ganti
                                    <input type="file" name="signature" accept="image/png,image/jpg,image/jpeg" class="sr-only">
                                </label>
                                <a href="{{ route('profile.signature.delete') }}"
                                    onclick="event.preventDefault(); if(confirm('Hapus tanda tangan?')) document.getElementById('del-sig').submit();"
                                    class="secondary-button" style="color:var(--danger-700);">
                                    <x-icon name="x" :size="14" /> Hapus
                                </a>
                                <form id="del-sig" method="POST" action="{{ route('profile.signature.delete') }}" style="display:none;">
                                    @csrf @method('DELETE')
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="profile-signature-empty">
                            <x-icon name="file-text" :size="20" />
                            <span>Belum ada tanda tangan. Unggah untuk dapat menyetujui SOP.</span>
                        </div>
                        <input type="file" name="signature" accept="image/png,image/jpg,image/jpeg" style="margin-top:8px;">
                    @endif
                    @error('signature') <em class="field-error">{{ $message }}</em> @enderror
                </div>

                <div class="form-actions">
                    <button type="submit" class="primary-button">Simpan Perubahan <x-icon name="arrow-right" :size="16" /></button>
                </div>
            </form>

            {{-- ===== Ganti Password ===== --}}
            <form method="POST" action="{{ route('profile.password') }}" class="profile-form" style="margin-top:28px;">
                @csrf @method('PUT')
                <h2>Ganti Kata Sandi</h2>

                @if (session('password_status'))
                    <div class="alert alert-success">{{ session('password_status') }}</div>
                @endif

                <label class="field">
                    <span>Kata Sandi Saat Ini</span>
                    <input type="password" name="current_password" autocomplete="current-password" required>
                    @error('current_password') <em class="field-error">{{ $message }}</em> @enderror
                </label>

                <label class="field">
                    <span>Kata Sandi Baru</span>
                    <input type="password" name="password" autocomplete="new-password" required>
                    @error('password') <em class="field-error">{{ $message }}</em> @enderror
                </label>

                <label class="field">
                    <span>Konfirmasi Kata Sandi Baru</span>
                    <input type="password" name="password_confirmation" autocomplete="new-password" required>
                </label>

                <div class="form-actions">
                    <button type="submit" class="primary-button">Ubah Kata Sandi <x-icon name="arrow-right" :size="16" /></button>
                </div>
            </form>
        </div>
    </main>
</x-knowledge-base.layout>
