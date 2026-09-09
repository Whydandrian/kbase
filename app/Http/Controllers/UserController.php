<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()->orderBy('name')->get();

        return view('admin.users.index', ['users' => $users]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        User::create([
            'name' => $request->string('name')->value(),
            'email' => $request->string('email')->value(),
            'jabatan' => $request->string('jabatan')->value() ?: null,
            'password' => Hash::make($request->string('password')->value()),
            'is_admin' => $request->boolean('is_admin'),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Pengguna berhasil ditambahkan.');
    }

    public function destroy(User $user): RedirectResponse
    {
        // Prevent an admin from deleting themselves.
        abort_if($user->id === auth()->id(), 403, 'Tidak dapat menghapus akun sendiri.');

        // Clean up the user's signature file if present.
        if ($user->signature_path && Storage::disk('local')->exists($user->signature_path)) {
            Storage::disk('local')->delete($user->signature_path);
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Pengguna dihapus.');
    }
}
