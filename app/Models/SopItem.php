<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SopItem extends Model
{
    protected $fillable = [
        'sop_id',
        'type',
        'content',
        'url',
        'file_path',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Sop, $this>
     */
    public function sop(): BelongsTo
    {
        return $this->belongsTo(Sop::class);
    }

    public function hasFile(): bool
    {
        return (bool) $this->file_path;
    }
}
