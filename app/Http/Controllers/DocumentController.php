<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Models\Category;
use App\Models\Document;
use App\Models\Domain;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    /**
     * Store a new document under a category.
     */
    public function store(StoreDocumentRequest $request, Category $category): RedirectResponse
    {
        $data = $this->baseData($request);
        $data['category_id'] = $category->id;
        $data['slug'] = $this->uniqueSlug($request->string('title')->value());

        if ($request->hasFile('document')) {
            $data['file_path'] = $request->file('document')->store('documents', 'local');
            $data['url'] = null;
        }

        Document::create($data);

        return $this->backToCategory($category, 'Dokumen berhasil ditambahkan.');
    }

    /**
     * Update an existing document.
     */
    public function update(StoreDocumentRequest $request, Document $document): RedirectResponse
    {
        $data = $this->baseData($request);

        if ($request->hasFile('document')) {
            $this->deleteFile($document);
            $data['file_path'] = $request->file('document')->store('documents', 'local');
            $data['url'] = null;
        } elseif ($request->filled('url')) {
            // Switching to a URL source removes any previously stored file.
            $this->deleteFile($document);
            $data['file_path'] = null;
        }

        $document->update($data);

        return $this->backToCategory($document->category, 'Dokumen berhasil diperbarui.');
    }

    /**
     * Toggle a document's public visibility.
     */
    public function toggle(Document $document): RedirectResponse
    {
        $document->update(['is_active' => ! $document->is_active]);

        $state = $document->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return $this->backToCategory($document->category, "Dokumen {$state}.");
    }

    /**
     * Delete a document and its stored file.
     */
    public function destroy(Document $document): RedirectResponse
    {
        $category = $document->category;
        $this->deleteFile($document);
        $document->delete();

        return $this->backToCategory($category, 'Dokumen dihapus.');
    }

    /**
     * Stream the private PDF for inline preview / download.
     * Non-admins may only view files of active documents.
     */
    public function file(Document $document): StreamedResponse
    {
        abort_unless($document->hasUploadedFile(), 404);

        $isAdmin = request()->user()?->isAdmin() ?? false;
        abort_if(! $document->is_active && ! $isAdmin, 403);

        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->response(
            $document->file_path,
            Str::slug($document->title).'.pdf',
            ['Content-Type' => 'application/pdf'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function baseData(StoreDocumentRequest $request): array
    {
        return [
            'title' => $request->string('title')->value(),
            'description' => $request->string('description')->value() ?: null,
            'doc_category' => $request->string('doc_category')->value() ?: null,
            'owner' => $request->string('owner')->value() ?: null,
            'url' => $request->string('url')->value() ?: null,
            'icon' => 'file-text',
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function deleteFile(Document $document): void
    {
        if ($document->file_path && Storage::disk('local')->exists($document->file_path)) {
            Storage::disk('local')->delete($document->file_path);
        }
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 2;

        while (Document::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    private function backToCategory(Category $category, string $status): RedirectResponse
    {
        $domain = $category->domain()->first() ?? $category->domain;

        $routeName = $domain->type === Domain::TYPE_TEAM
            ? 'knowledge-base.team'
            : 'knowledge-base.domain';

        return redirect()
            ->route($routeName, $domain)
            ->with('status', $status);
    }
}
