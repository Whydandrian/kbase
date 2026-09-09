<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SopApproval extends Model
{
    protected $fillable = [
        'sop_id',
        'role',
        'user_id',
        'nama',
        'jabatan',
        'tanggal',
        'signature_path',
        'is_signed',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'is_signed' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Sop, $this>
     */
    public function sop(): BelongsTo
    {
        return $this->belongsTo(Sop::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hasSignature(): bool
    {
        return $this->is_signed && (bool) $this->signature_path;
    }
}
