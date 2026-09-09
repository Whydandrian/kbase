<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SopFlowStep extends Model
{
    protected $fillable = [
        'sop_id',
        'step_no',
        'kegiatan',
        'pelaksana',
        'is_decision',
        'kelengkapan',
        'waktu',
        'output',
        'keterangan',
        'image_path',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_decision' => 'boolean',
            'step_no' => 'integer',
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

    public function hasImage(): bool
    {
        return (bool) $this->image_path;
    }
}
