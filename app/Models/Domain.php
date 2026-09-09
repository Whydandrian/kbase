<?php

namespace App\Models;

use Database\Factories\DomainFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Domain extends Model
{
    /** @use HasFactory<DomainFactory> */
    use HasFactory;

    public const TYPE_DOMAIN = 'domain';

    public const TYPE_TEAM = 'team';

    public const TYPE_ARCHIVE = 'archive';

    protected $fillable = [
        'name',
        'slug',
        'type',
        'description',
        'icon',
        'accent',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return HasMany<Category, $this>
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * @return HasManyThrough<Document, Category, $this>
     */
    public function documents(): HasManyThrough
    {
        return $this->hasManyThrough(Document::class, Category::class);
    }

    /**
     * @param  Builder<Domain>  $query
     */
    public function scopeOfType(Builder $query, string $type): void
    {
        $query->where('type', $type);
    }

    /**
     * @param  Builder<Domain>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('name');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
