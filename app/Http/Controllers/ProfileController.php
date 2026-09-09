<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfileController extends Controller
{
    public function show(): View
    {
        return view('profile.show', ['user' => auth()->user()]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        $data = [
            'name' => $request->string('name')->value(),
            'email' => $request->string('email')->value(),
            'jabatan' => $request->string('jabatan')->value() ?: null,
        ];

        if ($request->hasFile('signature')) {
            // Delete the old signature before storing the new one.
            if ($user->signature_path && Storage::disk('local')->exists($user->signature_path)) {
                Storage::disk('local')->delete($user->signature_path);
            }

            $data['signature_path'] = $request->file('signature')->store('signatures', 'local');
        }

        $user->update($data);

        return back()->with('status', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => Hash::make($request->string('password')->value()),
        ]);

        return back()->with('password_status', 'Kata sandi berhasil diubah.');
    }

    /**
     * Stream the user's own signature image from the private disk.
     */
    public function signature(): StreamedResponse
    {
        $user = auth()->user();

        abort_unless($user->signature_path && Storage::disk('local')->exists($user->signature_path), 404);

        return Storage::disk('local')->response($user->signature_path);
    }

    /**
     * Delete the stored signature image.
     */
    public function deleteSignature(): RedirectResponse
    {
        $user = auth()->user();

        if ($user->signature_path && Storage::disk('local')->exists($user->signature_path)) {
            Storage::disk('local')->delete($user->signature_path);
        }

        $user->update(['signature_path' => null]);

        return back()->with('status', 'Tanda tangan dihapus.');
    }
}
