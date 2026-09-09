<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSopRequest;
use App\Models\Document;
use App\Models\Sop;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SopController extends Controller
{
    public function index(): View
    {
        $sops = Sop::query()
            ->with('document')
            ->orderByDesc('created_at')
            ->get();

        return view('sop.index', ['sops' => $sops]);
    }

    public function show(Sop $sop): View
    {
        $sop->load(['approvals.user', 'items', 'flowSteps', 'document']);

        return view('sop.show', ['sop' => $sop]);
    }

    public function create(): View
    {
        return view('sop.form', [
            'sop' => new Sop(['status' => 'draft']),
            'documents' => $this->sopDocuments(),
        ]);
    }

    public function store(StoreSopRequest $request): RedirectResponse
    {
        $sop = DB::transaction(function () use ($request): Sop {
            $sop = Sop::create($this->identityData($request) + [
                'created_by' => $request->user()->id,
                'status' => 'draft',
            ]);

            $this->syncChildren($sop, $request);

            return $sop;
        });

        return redirect()->route('sop.show', $sop)->with('status', 'SOP berhasil dibuat.');
    }

    public function edit(Sop $sop): View
    {
        $sop->load(['approvals', 'items', 'flowSteps']);

        return view('sop.form', [
            'sop' => $sop,
            'documents' => $this->sopDocuments(),
        ]);
    }

    public function update(StoreSopRequest $request, Sop $sop): RedirectResponse
    {
        DB::transaction(function () use ($request, $sop): void {
            $sop->update($this->identityData($request));
            $this->syncChildren($sop, $request);
        });

        return redirect()->route('sop.show', $sop)->with('status', 'SOP berhasil diperbarui.');
    }

    public function destroy(Sop $sop): RedirectResponse
    {
        // Clean up any private files owned by this SOP.
        foreach ($sop->items as $item) {
            $this->deleteFile($item->file_path);
        }
        foreach ($sop->flowSteps as $step) {
            $this->deleteFile($step->image_path);
        }
        foreach ($sop->approvals as $approval) {
            $this->deleteFile($approval->signature_path);
        }
        $this->deleteFile($sop->final_document_path);

        $sop->delete();

        return redirect()->route('sop.index')->with('status', 'SOP dihapus.');
    }

    /**
     * The assigned pemeriksa (or an admin) approves and signs the SOP.
     */
    public function approve(Request $request, Sop $sop): RedirectResponse
    {
        $user = $request->user();
        $pemeriksa = $sop->approvalFor('pemeriksa');

        abort_unless($pemeriksa, 404);

        $isAssigned = $pemeriksa->user_id === $user->id;
        abort_unless($isAssigned || $user->isAdmin(), 403, 'Hanya pemeriksa yang ditunjuk atau administrator yang dapat menyetujui.');

        abort_unless($user->hasSignature(), 422, 'Anda belum memiliki tanda tangan. Unggah tanda tangan di profil terlebih dahulu.');

        // Snapshot the signer's signature onto the approval record.
        $pemeriksa->update([
            'user_id' => $user->id,
            'nama' => $pemeriksa->nama ?: $user->name,
            'jabatan' => $pemeriksa->jabatan ?: $user->jabatan,
            'tanggal' => now()->toDateString(),
            'signature_path' => $user->signature_path,
            'is_signed' => true,
        ]);

        $sop->update(['status' => 'reviewed']);

        return back()->with('status', 'SOP disetujui dan ditandatangani oleh pemeriksa.');
    }

    /**
     * Upload the final signed & stamped document from leadership.
     */
    public function uploadFinal(Request $request, Sop $sop): RedirectResponse
    {
        $request->validate([
            'final_document' => ['required', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:20480'],
        ]);

        $this->deleteFile($sop->final_document_path);

        $sop->update([
            'final_document_path' => $request->file('final_document')->store('sops/final', 'local'),
            'status' => 'published',
        ]);

        return back()->with('status', 'Dokumen final yang telah disahkan berhasil diunggah.');
    }

    /**
     * Render the SOP as a downloadable PDF (dompdf).
     */
    public function pdf(Sop $sop): Response
    {
        $sop->load(['approvals', 'items', 'flowSteps']);

        $pdf = Pdf::loadView('sop.pdf', ['sop' => $sop])->setPaper('a4', 'portrait');

        return $pdf->download('SOP-'.str($sop->nama_sop)->slug().'.pdf');
    }

    /**
     * Stream a private file that belongs to a SOP (final doc, item file, step image).
     */
    public function file(Request $request, Sop $sop, string $kind, int $id = 0): StreamedResponse
    {
        $path = match ($kind) {
            'final' => $sop->final_document_path,
            'item' => $sop->items()->whereKey($id)->value('file_path'),
            'step' => $sop->flowSteps()->whereKey($id)->value('image_path'),
            'signature' => $sop->approvals()->whereKey($id)->value('signature_path'),
            default => null,
        };

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }

    /**
     * @return array<string, mixed>
     */
    private function identityData(StoreSopRequest $request): array
    {
        return [
            'nomor_sop' => $request->string('nomor_sop')->value(),
            'nama_sop' => $request->string('nama_sop')->value(),
            'unit_pembuat' => $request->string('unit_pembuat')->value() ?: null,
            'kementerian' => $request->string('kementerian')->value() ?: null,
            'institusi' => $request->string('institusi')->value() ?: null,
            'tgl_pembuatan' => $request->date('tgl_pembuatan'),
            'tgl_revisi' => $request->date('tgl_revisi'),
            'tgl_efektif' => $request->date('tgl_efektif'),
            'document_id' => $request->integer('document_id') ?: null,
        ];
    }

    /**
     * Replace approvals, items, and flow steps from the submitted form.
     */
    private function syncChildren(Sop $sop, StoreSopRequest $request): void
    {
        // Preserve existing signatures so editing metadata does not wipe them.
        $existingApprovals = $sop->approvals()->get()->keyBy('role');

        foreach (Sop::APPROVAL_ROLES as $role) {
            $input = $request->input("approvals.{$role}", []);
            $current = $existingApprovals->get($role);

            $sop->approvals()->updateOrCreate(
                ['role' => $role],
                [
                    'nama' => $input['nama'] ?? $current?->nama,
                    'jabatan' => $input['jabatan'] ?? $current?->jabatan,
                    'tanggal' => $input['tanggal'] ?? $current?->tanggal,
                    // Keep any existing signature snapshot.
                    'user_id' => $current?->user_id,
                    'signature_path' => $current?->signature_path,
                    'is_signed' => (bool) ($current?->is_signed ?? false),
                ],
            );
        }

        // Items: wipe and recreate (simplest reliable sync for repeatable rows).
        foreach ($sop->items as $item) {
            $this->deleteFile($item->file_path);
        }
        $sop->items()->delete();

        foreach (array_values($request->input('items', [])) as $order => $item) {
            if (empty($item['content'])) {
                continue;
            }
            $sop->items()->create([
                'type' => $item['type'],
                'content' => $item['content'],
                'url' => $item['url'] ?? null,
                'sort_order' => $order,
            ]);
        }

        // Flow steps (with optional uploaded image per step).
        foreach ($sop->flowSteps as $step) {
            $this->deleteFile($step->image_path);
        }
        $sop->flowSteps()->delete();

        foreach (array_values($request->input('steps', [])) as $order => $step) {
            if (empty($step['kegiatan'])) {
                continue;
            }

            $imagePath = null;
            $file = $request->file("steps.{$order}.image");
            if ($file) {
                $imagePath = $file->store('sops/flow', 'local');
            }

            $sop->flowSteps()->create([
                'step_no' => $order + 1,
                'kegiatan' => $step['kegiatan'],
                'pelaksana' => $step['pelaksana'] ?? null,
                'is_decision' => (bool) ($step['is_decision'] ?? false),
                'kelengkapan' => $step['kelengkapan'] ?? null,
                'waktu' => $step['waktu'] ?? null,
                'output' => $step['output'] ?? null,
                'keterangan' => $step['keterangan'] ?? null,
                'image_path' => $imagePath,
                'sort_order' => $order,
            ]);
        }
    }

    private function deleteFile(?string $path): void
    {
        if ($path && Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }
    }

    /**
     * Documents from the "Dokumen SOP" category, for optional linking.
     *
     * @return Collection<int, Document>
     */
    private function sopDocuments()
    {
        return Document::query()
            ->whereHas('category', fn ($q) => $q->where('slug', 'tata-kelola-ti-dokumen-sop'))
            ->orderBy('title')
            ->get(['id', 'title']);
    }
}
