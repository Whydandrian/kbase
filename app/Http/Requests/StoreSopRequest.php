<?php

namespace App\Http\Requests;

use App\Models\Sop;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSopRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Any authenticated user may create or edit a SOP.
        return (bool) $this->user();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nomor_sop' => ['required', 'string', 'max:255'],
            'nama_sop' => ['required', 'string', 'max:255'],
            'unit_pembuat' => ['nullable', 'string', 'max:255'],
            'kementerian' => ['nullable', 'string', 'max:255'],
            'institusi' => ['nullable', 'string', 'max:255'],
            'tgl_pembuatan' => ['nullable', 'date'],
            'tgl_revisi' => ['nullable', 'date'],
            'tgl_efektif' => ['nullable', 'date'],
            'document_id' => ['nullable', 'exists:documents,id'],

            // Approvals: penyusun / pemeriksa / pengesahan
            'approvals' => ['array'],
            'approvals.*.nama' => ['nullable', 'string', 'max:255'],
            'approvals.*.jabatan' => ['nullable', 'string', 'max:255'],
            'approvals.*.tanggal' => ['nullable', 'date'],

            // Repeatable items
            'items' => ['array'],
            'items.*.type' => ['required_with:items', Rule::in(array_keys(Sop::ITEM_TYPES))],
            'items.*.content' => ['required_with:items', 'string', 'max:2000'],
            'items.*.url' => ['nullable', 'url', 'max:2048'],

            // Flow steps
            'steps' => ['array'],
            'steps.*.kegiatan' => ['required_with:steps', 'string', 'max:1000'],
            'steps.*.pelaksana' => ['nullable', 'string', 'max:255'],
            'steps.*.is_decision' => ['nullable', 'boolean'],
            'steps.*.kelengkapan' => ['nullable', 'string', 'max:1000'],
            'steps.*.waktu' => ['nullable', 'string', 'max:255'],
            'steps.*.output' => ['nullable', 'string', 'max:1000'],
            'steps.*.keterangan' => ['nullable', 'string', 'max:1000'],
            'steps.*.image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nomor_sop' => 'nomor SOP',
            'nama_sop' => 'nama SOP',
        ];
    }
}
