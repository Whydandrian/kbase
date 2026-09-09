<?php

use App\Models\Sop;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

function loginUser(array $attributes = []): User
{
    return User::factory()->create($attributes + ['is_admin' => false]);
}

it('shows the public SOP list and detail', function () {
    $sop = Sop::factory()->create(['nama_sop' => 'Penyusunan Program Kerja']);

    get(route('sop.index'))->assertOk()->assertSee('Penyusunan Program Kerja');
    get(route('sop.show', $sop))->assertOk()->assertSee($sop->nomor_sop);
});

it('requires login to create a SOP', function () {
    get(route('sop.create'))->assertRedirect(route('login'));
    post(route('sop.store'), [])->assertRedirect(route('login'));
});

it('lets any authenticated user create a SOP with items and steps', function () {
    actingAs(loginUser())
        ->post(route('sop.store'), [
            'nomor_sop' => '999/IT10/2026',
            'nama_sop' => 'SOP Uji Coba',
            'items' => [
                'a' => ['type' => 'dasar_hukum', 'content' => 'UU Contoh'],
                'b' => ['type' => 'peralatan', 'content' => 'Komputer'],
            ],
            'steps' => [
                ['kegiatan' => 'Langkah pertama', 'pelaksana' => 'Staff'],
            ],
            'approvals' => [
                'penyusun' => ['nama' => 'Budi', 'jabatan' => 'Staff'],
            ],
        ])->assertRedirect();

    $sop = Sop::query()->where('nomor_sop', '999/IT10/2026')->firstOrFail();
    expect($sop->items)->toHaveCount(2)
        ->and($sop->flowSteps)->toHaveCount(1)
        ->and($sop->approvalFor('penyusun')->nama)->toBe('Budi');
});

it('lets the assigned pemeriksa approve and sign', function () {
    Storage::fake('local');
    $reviewer = loginUser(['signature_path' => 'signatures/rev.png']);
    Storage::disk('local')->put('signatures/rev.png', 'img');

    $sop = Sop::factory()->create();
    $sop->approvals()->create(['role' => 'pemeriksa', 'user_id' => $reviewer->id, 'nama' => 'Reviewer', 'is_signed' => false]);

    actingAs($reviewer)->post(route('sop.approve', $sop))->assertRedirect();

    $approval = $sop->approvalFor('pemeriksa')->fresh();
    expect($approval->is_signed)->toBeTrue()
        ->and($sop->fresh()->status)->toBe('reviewed');
});

it('blocks a non-assigned user from approving', function () {
    $assignedReviewer = loginUser();
    $sop = Sop::factory()->create();
    $sop->approvals()->create(['role' => 'pemeriksa', 'user_id' => $assignedReviewer->id, 'nama' => 'Someone']);

    // A different, non-admin user must not be able to approve.
    actingAs(loginUser(['signature_path' => 'x.png']))
        ->post(route('sop.approve', $sop))
        ->assertForbidden();
});

it('uploads the final signed document and publishes', function () {
    Storage::fake('local');
    $sop = Sop::factory()->create();

    actingAs(loginUser())
        ->post(route('sop.final', $sop), [
            'final_document' => UploadedFile::fake()->create('final.pdf', 200, 'application/pdf'),
        ])->assertRedirect();

    $sop->refresh();
    expect($sop->final_document_path)->not->toBeNull()
        ->and($sop->status)->toBe('published');
    Storage::disk('local')->assertExists($sop->final_document_path);
});

it('exports a SOP to PDF', function () {
    $sop = Sop::factory()->create(['nama_sop' => 'SOP PDF']);

    $response = get(route('sop.pdf', $sop));
    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

it('streams a private step image only when it exists', function () {
    Storage::fake('local');
    $sop = Sop::factory()->create();
    $step = $sop->flowSteps()->create(['step_no' => 1, 'kegiatan' => 'X', 'image_path' => 'sops/flow/x.png']);
    Storage::disk('local')->put('sops/flow/x.png', 'imgdata');

    get(route('sop.file', [$sop, 'step', $step->id]))->assertOk();
    get(route('sop.file', [$sop, 'step', 999999]))->assertNotFound();
});

it('lets an authenticated user delete a SOP', function () {
    $sop = Sop::factory()->create();

    actingAs(loginUser())->delete(route('sop.destroy', $sop))->assertRedirect(route('sop.index'));
    $this->assertDatabaseMissing('sops', ['id' => $sop->id]);
});
