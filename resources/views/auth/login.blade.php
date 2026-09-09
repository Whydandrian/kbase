<x-knowledge-base.layout title="Masuk — IT Knowledge Base">
    <main class="page auth-page">
        <div class="auth-card">
            <div class="brand-symbol"><x-icon name="shield-check" :size="20" /></div>
            <h1>Masuk Administrator</h1>
            <p class="auth-sub">Hanya administrator yang dapat mengelola dokumen.</p>

            <form method="POST" action="{{ route('login.store') }}" class="auth-form">
                @csrf
                <label class="field">
                    <span>Email</span>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
                    @error('email') <em class="field-error">{{ $message }}</em> @enderror
                </label>

                <label class="field">
                    <span>Kata Sandi</span>
                    <input type="password" name="password" required autocomplete="current-password">
                    @error('password') <em class="field-error">{{ $message }}</em> @enderror
                </label>

                <label class="field-check">
                    <input type="checkbox" name="remember" value="1"> <span>Ingat saya</span>
                </label>

                <button type="submit" class="primary-button auth-submit">Masuk <x-icon name="arrow-right" :size="16" /></button>
            </form>
        </div>
    </main>
</x-knowledge-base.layout>
