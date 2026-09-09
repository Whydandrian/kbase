<?php

use App\Models\Category;
use App\Models\Document;
use App\Models\Domain;
use App\Models\Thesis;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

it('shows the home page with domain, team, and archive groups', function () {
    Domain::factory()->domain()->create(['name' => 'Tata Kelola TI']);
    Domain::factory()->team()->create(['name' => 'Web Developer']);
    Domain::factory()->archive()->create(['name' => 'Arsip TA']);

    get(route('knowledge-base'))
        ->assertOk()
        ->assertSee('Tata Kelola TI')
        ->assertSee('Web Developer')
        ->assertSee('Arsip Tugas Akhir');
});

it('lists managerial domains but not teams as cards', function () {
    Domain::factory()->domain()->create(['name' => 'Manajemen Layanan TI']);
    $team = Domain::factory()->team()->create(['name' => 'Tim Rahasia']);

    $response = get(route('knowledge-base.domains'))
        ->assertOk()
        ->assertSee('Manajemen Layanan TI');

    // The team must not be rendered as a linked card on the domain index.
    expect($response->getContent())->not->toContain(route('knowledge-base.team', $team));
});

it('shows a domain detail with its categories and documents', function () {
    $domain = Domain::factory()->domain()->create(['name' => 'Tata Kelola TI']);
    $category = Category::factory()->for($domain)->create(['name' => 'Dokumen SOP']);
    Document::factory()->for($category)->create(['title' => 'SOP Backup Data']);

    get(route('knowledge-base.domain', $domain))
        ->assertOk()
        ->assertSee('Dokumen SOP')
        ->assertSee('SOP Backup Data');
});

it('does not expose the archive domain via the domain detail route', function () {
    $archive = Domain::factory()->archive()->create(['name' => 'Arsip TA']);

    get(route('knowledge-base.domain', $archive))->assertNotFound();
});

it('filters theses by year and study program on the archive page', function () {
    Thesis::factory()->create(['title' => 'TA Informatika 2024', 'study_program' => 'Informatika', 'year' => 2024]);
    Thesis::factory()->create(['title' => 'TA Elektro 2020', 'study_program' => 'Teknik Elektro', 'year' => 2020]);

    // Card titles render inside an <h3> text node; the global search JSON
    // holds them as quoted strings, so match the rendered heading form.
    get(route('knowledge-base.archive', ['study_program' => 'Informatika']))
        ->assertOk()
        ->assertSee('>TA Informatika 2024<', false)
        ->assertDontSee('>TA Elektro 2020<', false);

    get(route('knowledge-base.archive', ['year' => 2020]))
        ->assertOk()
        ->assertSee('>TA Elektro 2020<', false)
        ->assertDontSee('>TA Informatika 2024<', false);
});

it('stores a thesis submitted with a drive url', function () {
    post(route('knowledge-base.archive.store'), [
        'title' => 'Analisis Sistem Informasi',
        'author' => 'Budi Santoso',
        'university' => 'Institut Teknologi Kalimantan',
        'study_program' => 'Sistem Informasi',
        'year' => 2025,
        'drive_url' => 'https://drive.google.com/file/d/abc',
    ])->assertRedirect(route('knowledge-base.archive'));

    $this->assertDatabaseHas('theses', [
        'title' => 'Analisis Sistem Informasi',
        'drive_url' => 'https://drive.google.com/file/d/abc',
        'file_path' => null,
    ]);
});

it('stores a thesis submitted with an uploaded pdf', function () {
    Storage::fake('public');

    post(route('knowledge-base.archive.store'), [
        'title' => 'Rancang Bangun Aplikasi',
        'author' => 'Siti Aminah',
        'university' => 'Universitas Mulawarman',
        'study_program' => 'Informatika',
        'year' => 2023,
        'document' => UploadedFile::fake()->create('ta.pdf', 500, 'application/pdf'),
    ])->assertRedirect(route('knowledge-base.archive'));

    $thesis = Thesis::query()->where('title', 'Rancang Bangun Aplikasi')->firstOrFail();

    expect($thesis->file_path)->not->toBeNull();
    Storage::disk('public')->assertExists($thesis->file_path);
});

it('rejects a thesis without a drive url or uploaded file', function () {
    post(route('knowledge-base.archive.store'), [
        'title' => 'Tanpa Berkas',
        'author' => 'Anonim',
        'university' => 'ITK',
        'study_program' => 'Informatika',
        'year' => 2024,
    ])->assertSessionHasErrors('document');

    $this->assertDatabaseMissing('theses', ['title' => 'Tanpa Berkas']);
});
