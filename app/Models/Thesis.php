<?php

namespace App\Models;

use Database\Factories\ThesisFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Thesis extends Model
{
    /** @use HasFactory<ThesisFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'university',
        'study_program',
        'year',
        'drive_url',
        'file_path',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
        ];
    }

    /**
     * Public URL to the uploaded PDF, when present.
     */
    public function fileUrl(): ?string
    {
        return $this->file_path ? Storage::disk('public')->url($this->file_path) : null;
    }

    public function hasUploadedFile(): bool
    {
        return (bool) $this->file_path;
    }

    /**
     * Free-text search across title, author, university, and study program.
     *
     * @param  Builder<Thesis>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);

        if ($term === '') {
            return;
        }

        $query->where(function (Builder $query) use ($term): void {
            foreach (['title', 'author', 'university', 'study_program'] as $column) {
                $query->orWhere($column, 'like', "%{$term}%");
            }
        });
    }

    /**
     * @param  Builder<Thesis>  $query
     */
    public function scopeForYear(Builder $query, int|string|null $year): void
    {
        if ($year !== null && $year !== '') {
            $query->where('year', $year);
        }
    }

    /**
     * @param  Builder<Thesis>  $query
     */
    public function scopeForStudyProgram(Builder $query, ?string $program): void
    {
        if ($program !== null && $program !== '') {
            $query->where('study_program', $program);
        }
    }
}
