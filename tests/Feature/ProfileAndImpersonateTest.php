<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

function makeAdmin(array $attrs = []): User
{
    return User::factory()->create(['is_admin' => true, ...$attrs]);
}

function makeUser(array $attrs = []): User
{
    return User::factory()->create(['is_admin' => false, ...$attrs]);
}

// ===== Profile =====

it('shows the profile page to any authenticated user', function () {
    actingAs(makeUser())->get(route('profile.show'))->assertOk();
});

it('requires login to view profile', function () {
    get(route('profile.show'))->assertRedirect(route('login'));
});

it('updates name, email, and jabatan', function () {
    $user = makeUser(['name' => 'Lama', 'jabatan' => null]);

    actingAs($user)
        ->put(route('profile.update'), [
            'name' => 'Baru',
            'email' => $user->email,
            'jabatan' => 'Staf TI',
        ])->assertRedirect();

    expect($user->fresh()->name)->toBe('Baru')
        ->and($user->fresh()->jabatan)->toBe('Staf TI');
});

it('uploads a signature image to the private disk', function () {
    Storage::fake('local');
    $user = makeUser();

    actingAs($user)
        ->put(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'signature' => UploadedFile::fake()->image('ttd.png'),
        ])->assertRedirect();

    $user->refresh();
    expect($user->signature_path)->not->toBeNull();
    Storage::disk('local')->assertExists($user->signature_path);
});

it('deletes an existing signature', function () {
    Storage::fake('local');
    Storage::disk('local')->put('signatures/ttd.png', 'img');
    $user = makeUser(['signature_path' => 'signatures/ttd.png']);

    actingAs($user)->delete(route('profile.signature.delete'))->assertRedirect();

    expect($user->fresh()->signature_path)->toBeNull();
    Storage::disk('local')->assertMissing('signatures/ttd.png');
});

it('changes the password with the correct current password', function () {
    $user = makeUser(['password' => 'oldpassword']);

    actingAs($user)
        ->put(route('profile.password'), [
            'current_password' => 'oldpassword',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ])->assertRedirect();

    expect(Hash::check('newpassword', $user->fresh()->password))->toBeTrue();
});

it('rejects password change with wrong current password', function () {
    $user = makeUser(['password' => 'realpassword']);

    actingAs($user)
        ->put(route('profile.password'), [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ])->assertSessionHasErrors('current_password');
});

// ===== Impersonate =====

it('admin can start impersonating another user', function () {
    $admin = makeAdmin();
    $target = makeUser(['name' => 'Pengguna Biasa']);

    actingAs($admin)
        ->post(route('impersonate.start', $target))
        ->assertRedirect(route('knowledge-base'));

    // After impersonation, the session holds the admin's real ID.
    expect(session('impersonating_id'))->toBe($admin->id);
    // The current auth user is now the target.
    expect(auth()->id())->toBe($target->id);
});

it('admin can stop impersonating and return to their account', function () {
    $admin = makeAdmin();
    $target = makeUser();

    actingAs($admin)->post(route('impersonate.start', $target));

    post(route('impersonate.stop'))->assertRedirect(route('knowledge-base'));

    expect(session()->has('impersonating_id'))->toBeFalse();
    expect(auth()->id())->toBe($admin->id);
});

it('non-admin cannot impersonate another user', function () {
    $user = makeUser();
    $other = makeUser();

    actingAs($user)
        ->post(route('impersonate.start', $other))
        ->assertForbidden();
});

it('admin cannot impersonate themselves', function () {
    $admin = makeAdmin();

    actingAs($admin)
        ->post(route('impersonate.start', $admin))
        ->assertStatus(400);
});
