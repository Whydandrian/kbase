<?php

use App\Models\Category;
use App\Models\Document;
use App\Models\Domain;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

function admin(): User
{
    return User::factory()->create(['is_admin' => true]);
}

function member(): User
{
    return User::factory()->create(['is_admin' => false]);
}

it('hides inactive documents from the public but shows them to admins', function () {
    $domain = Domain::factory()->domain()->create();
    $category = Category::factory()->for($domain)->create();
    Document::factory()->for($category)->create(['title' => 'Dokumen Aktif', 'is_active' => true]);
    Document::factory()->for($category)->inactive()->create(['title' => 'Dokumen Nonaktif']);

    get(route('knowledge-base.domain', $domain))
        ->assertOk()
        ->assertSee('Dokumen Aktif')
        ->assertDontSee('Dokumen Nonaktif');

    actingAs(admin())
        ->get(route('knowledge-base.domain', $domain))
        ->assertOk()
        ->assertSee('Dokumen Aktif')
        ->assertSee('Dokumen Nonaktif');
});

it('forbids non-admins from creating documents', function () {
    $category = Category::factory()->create();

    post(route('documents.store', $category), [
        'title' => 'X', 'url' => 'https://example.com',
    ])->assertRedirect(route('login'));

    actingAs(member())
        ->post(route('documents.store', $category), [
            'title' => 'X', 'url' => 'https://example.com',
        ])->assertForbidden();

    $this->assertDatabaseCount('documents', 0);
});

it('lets an admin create a url document', function () {
    $category = Category::factory()->create();

    actingAs(admin())
        ->post(route('documents.store', $category), [
            'title' => 'Panduan VPN',
            'url' => 'https://example.com/vpn',
            'is_active' => '1',
        ])->assertRedirect();

    $this->assertDatabaseHas('documents', [
        'title' => 'Panduan VPN',
        'url' => 'https://example.com/vpn',
        'file_path' => null,
        'is_active' => true,
    ]);
});

it('stores an uploaded pdf on the private disk', function () {
    Storage::fake('local');
    $category = Category::factory()->create();

    actingAs(admin())
        ->post(route('documents.store', $category), [
            'title' => 'SOP Backup',
            'document' => UploadedFile::fake()->create('sop.pdf', 300, 'application/pdf'),
        ])->assertRedirect();

    $document = Document::firstOrFail();
    expect($document->file_path)->not->toBeNull()
        ->and($document->file_path)->toStartWith('documents/');
    Storage::disk('local')->assertExists($document->file_path);
});

it('requires a url or a pdf when creating', function () {
    $category = Category::factory()->create();

    actingAs(admin())
        ->post(route('documents.store', $category), ['title' => 'Kosong'])
        ->assertSessionHasErrors('document');

    $this->assertDatabaseCount('documents', 0);
});

it('lets an admin toggle and delete a document', function () {
    $document = Document::factory()->create(['is_active' => true]);

    actingAs(admin())->patch(route('documents.toggle', $document))->assertRedirect();
    expect($document->fresh()->is_active)->toBeFalse();

    actingAs(admin())->delete(route('documents.destroy', $document))->assertRedirect();
    $this->assertDatabaseMissing('documents', ['id' => $document->id]);
});

it('serves an active document pdf but blocks an inactive one for the public', function () {
    Storage::fake('local');
    Storage::disk('local')->put('documents/a.pdf', '%PDF-1.4 fake');
    Storage::disk('local')->put('documents/b.pdf', '%PDF-1.4 fake');

    $active = Document::factory()->create(['file_path' => 'documents/a.pdf', 'url' => null, 'is_active' => true]);
    $inactive = Document::factory()->create(['file_path' => 'documents/b.pdf', 'url' => null, 'is_active' => false]);

    get(route('documents.file', $active))->assertOk();
    get(route('documents.file', $inactive))->assertForbidden();
    actingAs(admin())->get(route('documents.file', $inactive))->assertOk();
});

it('allows an admin to log in and a wrong password to fail', function () {
    $user = User::factory()->create([
        'email' => 'admin@kbase.test',
        'password' => 'secret-pass',
        'is_admin' => true,
    ]);

    post(route('login.store'), ['email' => $user->email, 'password' => 'wrong'])
        ->assertSessionHasErrors('email');

    post(route('login.store'), ['email' => $user->email, 'password' => 'secret-pass'])
        ->assertRedirect(route('knowledge-base'));

    $this->assertAuthenticatedAs($user);
});
