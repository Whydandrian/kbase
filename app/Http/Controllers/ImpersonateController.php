<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonateController extends Controller
{
    /**
     * Admin starts impersonating a user.
     * Stores the admin's real ID in the session, then logs in as the target.
     */
    public function start(Request $request, User $user): RedirectResponse
    {
        $admin = $request->user();

        abort_unless($admin->isAdmin(), 403);
        abort_if($user->id === $admin->id, 400, 'Tidak dapat impersonate diri sendiri.');

        // Prevent nested impersonation.
        abort_if($request->session()->has('impersonating_id'), 400, 'Sudah dalam mode impersonate.');

        $request->session()->put('impersonating_id', $admin->id);

        Auth::loginUsingId($user->id);

        return redirect()
            ->route('knowledge-base')
            ->with('status', 'Anda sekarang masuk sebagai '.$user->name.'.');
    }

    /**
     * Stop impersonation and return to the original admin account.
     */
    public function stop(Request $request): RedirectResponse
    {
        $adminId = $request->session()->pull('impersonating_id');

        abort_unless($adminId, 403, 'Tidak sedang dalam mode impersonate.');

        Auth::loginUsingId($adminId);

        return redirect()
            ->route('knowledge-base')
            ->with('status', 'Kembali ke akun administrator.');
    }
}
