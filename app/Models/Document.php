<?php

namespace App\Models;

use Database\Factories\DocumentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Document extends Model
{
    /** @use HasFactory<DocumentFactory> */
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'description',
        'doc_category',
        'owner',
        'icon',
        'url',
        'file_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasOne<Sop, $this>
     */
    public function sop(): HasOne
    {
        return $this->hasOne(Sop::class);
    }

    /**
     * Only documents visible to the public.
     *
     * @param  Builder<Document>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function hasUploadedFile(): bool
    {
        return (bool) $this->file_path;
    }

    /**
     * How the document should be opened: 'pdf' (private preview) or 'url'.
     */
    public function sourceMode(): ?string
    {
        if ($this->hasUploadedFile()) {
            return 'pdf';
        }

        if ($this->url) {
            return 'url';
        }

        return null;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
