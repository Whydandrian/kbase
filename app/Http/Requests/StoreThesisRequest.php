<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreThesisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'university' => ['required', 'string', 'max:255'],
            'study_program' => ['required', 'string', 'max:255'],
            'year' => ['required', 'integer', 'min:1980', 'max:'.(date('Y') + 1)],
            'drive_url' => ['nullable', 'url', 'max:2048'],
            'document' => ['nullable', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:20480'],
        ];
    }

    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->filled('drive_url') && ! $this->hasFile('document')) {
                $validator->errors()->add(
                    'document',
                    'Sertakan tautan Google Drive atau unggah berkas PDF.',
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
            'title' => 'judul TA',
            'author' => 'penulis',
            'university' => 'perguruan tinggi',
            'study_program' => 'program studi',
            'year' => 'tahun penelitian',
            'drive_url' => 'tautan Google Drive',
            'document' => 'berkas PDF',
        ];
    }
}
