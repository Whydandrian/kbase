<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'doc_category' => ['nullable', 'string', 'max:100'],
            'owner' => ['nullable', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'max:2048'],
            'document' => ['nullable', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:20480'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            // On create, one of URL / PDF is required. On update, the document
            // may already have a stored source, so this is enforced separately.
            if ($this->routeIs('*.store')
                && ! $this->filled('url')
                && ! $this->hasFile('document')) {
                $validator->errors()->add(
                    'document',
                    'Sertakan tautan URL atau unggah berkas PDF.',
                );
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'judul dokumen',
            'url' => 'tautan URL',
            'document' => 'berkas PDF',
        ];
    }
}
